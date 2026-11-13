<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// Phase 1: static UI only. Every other route still renders a view directly (no controllers, no data layer).
// Route parameters (e.g. {id}) are passed straight into the view.

Route::redirect('/', '/login');

// User auth (users table, guard: web)
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// SaaS admin auth (admins table, guard: admin)
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'login']);
});
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::middleware('auth:web,admin')->group(function () {
    Route::view('/profile', 'profile')->name('profile');
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Tenants: dynamic (database-backed via TenantController).
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/archived', [TenantController::class, 'archived'])->name('tenants.archived');
    Route::post('/tenants/{id}/restore', [TenantController::class, 'restore'])->whereNumber('id')->name('tenants.restore');
    Route::get('/tenants/{id}', [TenantController::class, 'show'])->whereNumber('id')->name('tenants.show');
    Route::get('/tenants/{id}/edit', [TenantController::class, 'edit'])->whereNumber('id')->name('tenants.edit');
    Route::put('/tenants/{id}', [TenantController::class, 'update'])->whereNumber('id')->name('tenants.update');
    Route::delete('/tenants/{id}', [TenantController::class, 'destroy'])->whereNumber('id')->name('tenants.destroy');

    $resources = ['plans', 'branches', 'halls', 'staff', 'students'];

    foreach ($resources as $name) {
        Route::view("/{$name}", "{$name}.index")->name("{$name}.index");
        Route::view("/{$name}/create", "{$name}.form", ['mode' => 'create'])->name("{$name}.create");
        Route::view("/{$name}/{id}/edit", "{$name}.form", ['mode' => 'edit'])->whereNumber('id')->name("{$name}.edit");

        if (in_array($name, ['branches', 'halls', 'students'])) {
            Route::view("/{$name}/{id}", "{$name}.show")->whereNumber('id')->name("{$name}.show");
        }
    }

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', '/settings/fee-structure');
        Route::view('/fee-structure', 'settings.fee-structure')->name('fees');
        Route::view('/general', 'settings.general')->name('general');
        Route::view('/roles', 'settings.roles')->name('roles');
    });
});
