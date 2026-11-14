<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('viewer_can')) {
    /**
     * Whether the current principal (SaaS admin or tenant user) is granted
     * this module/action. Used inline in Blade attribute expressions where
     * the @canDo directive (block-level only) can't be used, e.g.
     * :edit="viewer_can('branches', 'update') ? route(...) : null".
     */
    function viewer_can(string $module, string $action = 'view'): bool
    {
        $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

        return $user?->canDo($module, $action) ?? false;
    }
}
