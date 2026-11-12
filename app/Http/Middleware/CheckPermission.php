<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless(auth()->check() && auth()->user()->hasPermission($permission), 403, __('ui.forbidden'));
        return $next($request);
    }
}
