<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeout = (int) \App\Models\Setting::get('session_timeout', 30);
            $last = session('last_activity_at', time());

            if ($timeout > 0 && (time() - $last) > ($timeout * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['username' => __('ui.session_timeout')]);
            }

            session(['last_activity_at' => time()]);
        }

        return $next($request);
    }
}
