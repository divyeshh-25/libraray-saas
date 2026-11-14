<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'view'): Response
    {
        $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

        $allowed = $action === 'view'
            ? $user?->canAccessModule($module)
            : $user?->canDo($module, $action);

        abort_unless($allowed, 403);

        return $next($request);
    }
}
