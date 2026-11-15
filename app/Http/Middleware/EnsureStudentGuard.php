<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the student portal to web-guard users whose account is a
 * student. Owners and staff authenticate on the same guard/table but must
 * never reach this portal.
 */
class EnsureStudentGuard
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        abort_unless($user?->user_type === 'student' && $user->student !== null, 403);

        return $next($request);
    }
}
