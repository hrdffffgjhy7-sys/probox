<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeout = (int) config('session.timeout_minutes', 30);
            $last = $request->session()->get('last_activity');

            if ($last && now()->diffInMinutes($last, true) >= $timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['username' => __('auth.session_expired')]);
            }

            $request->session()->put('last_activity', now());
            if (Auth::user()->last_activity_at === null || Auth::user()->last_activity_at->diffInMinutes(now(), true) >= 1) {
                Auth::user()->forceFill(['last_activity_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}
