<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks the student portal's own guard scope from reaching the
 * owner/admin/staff area. Applied to the main authenticated route group;
 * the student portal's own group uses EnsureStudentGuard instead.
 */
class EnsureNotStudent
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        abort_if($user?->user_type === 'student', 403);

        return $next($request);
    }
}
