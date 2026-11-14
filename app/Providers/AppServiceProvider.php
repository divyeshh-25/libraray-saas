<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @canDo('branches', 'add') ... @endcanDo — lets views hide buttons
        // for actions the current principal (admin or web) isn't granted.
        Blade::if('canDo', function (string $module, string $action = 'view') {
            $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

            return $user?->canDo($module, $action) ?? false;
        });
    }
}
