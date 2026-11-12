<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, __('ui.forbidden'));
        return $next($request);
    }
}
