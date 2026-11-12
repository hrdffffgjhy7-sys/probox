<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request, AuditService $audit)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:'.Str::lower($credentials['username']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'username' => __('Too many login attempts. Please try again later.'),
            ]);
        }

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'username' => __('Invalid username or password.'),
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('last_activity', now());
        $request->session()->put('locale', $request->session()->get('locale', config('app.locale')));

        $user = Auth::user();
        $user->forceFill(['last_login_at' => now(), 'last_activity_at' => now()])->save();
        $audit->log('login', 'auth', $user, 'User signed in');

        if ($user->must_change_password) {
            return redirect()->route('password.change')->with('warning', __('Change Password'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request, AuditService $audit)
    {
        if (Auth::check()) {
            $audit->log('logout', 'auth', Auth::user(), 'User signed out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
