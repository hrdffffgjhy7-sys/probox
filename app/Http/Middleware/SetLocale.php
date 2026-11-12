<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale && auth()->check()) {
            $locale = auth()->user()->locale;
        }

        $locale = in_array($locale, ['en', 'ur'], true) ? $locale : config('app.locale');
        app()->setLocale($locale);

        view()->share('locale', $locale);
        view()->share('dir', $locale === 'ur' ? 'rtl' : 'ltr');
        view()->share('isRtl', $locale === 'ur');

        return $next($request);
    }
}
