<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\GeneralSettingController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
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

    // Tenants: dynamic (database-backed via TenantController). SaaS admin + anyone granted the module.
    Route::middleware('module:tenants')->group(function () {
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/archived', [TenantController::class, 'archived'])->name('tenants.archived');
        Route::post('/tenants/{id}/restore', [TenantController::class, 'restore'])->whereNumber('id')->name('tenants.restore');
        Route::get('/tenants/{id}', [TenantController::class, 'show'])->whereNumber('id')->name('tenants.show');
        Route::get('/tenants/{id}/edit', [TenantController::class, 'edit'])->whereNumber('id')->name('tenants.edit');
        Route::put('/tenants/{id}', [TenantController::class, 'update'])->whereNumber('id')->name('tenants.update');
        Route::delete('/tenants/{id}', [TenantController::class, 'destroy'])->whereNumber('id')->name('tenants.destroy');
    });

    // Plans: dynamic (database-backed via PlanController). SaaS admin + anyone granted the module.
    Route::middleware('module:plans')->group(function () {
        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/archived', [PlanController::class, 'archived'])->name('plans.archived');
        Route::post('/plans/{id}/restore', [PlanController::class, 'restore'])->whereNumber('id')->name('plans.restore');
        Route::get('/plans/{id}', [PlanController::class, 'show'])->whereNumber('id')->name('plans.show');
        Route::get('/plans/{id}/edit', [PlanController::class, 'edit'])->whereNumber('id')->name('plans.edit');
        Route::put('/plans/{id}', [PlanController::class, 'update'])->whereNumber('id')->name('plans.update');
        Route::delete('/plans/{id}', [PlanController::class, 'destroy'])->whereNumber('id')->name('plans.destroy');
    });

    // Branches: dynamic (database-backed via BranchController). Tenant-scoped, never for the SaaS admin.
    Route::middleware('module:branches')->group(function () {
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('/branches/archived', [BranchController::class, 'archived'])->name('branches.archived');
        Route::post('/branches/{id}/restore', [BranchController::class, 'restore'])->whereNumber('id')->name('branches.restore');
        Route::get('/branches/{id}', [BranchController::class, 'show'])->whereNumber('id')->name('branches.show');
        Route::get('/branches/{id}/edit', [BranchController::class, 'edit'])->whereNumber('id')->name('branches.edit');
        Route::put('/branches/{id}', [BranchController::class, 'update'])->whereNumber('id')->name('branches.update');
        Route::delete('/branches/{id}', [BranchController::class, 'destroy'])->whereNumber('id')->name('branches.destroy');
    });

    // Halls: dynamic (database-backed via HallController). Tenant-scoped, never for the SaaS admin.
    Route::middleware('module:halls')->group(function () {
        Route::get('/halls', [HallController::class, 'index'])->name('halls.index');
        Route::get('/halls/create', [HallController::class, 'create'])->name('halls.create');
        Route::post('/halls', [HallController::class, 'store'])->name('halls.store');
        Route::get('/halls/archived', [HallController::class, 'archived'])->name('halls.archived');
        Route::post('/halls/{id}/restore', [HallController::class, 'restore'])->whereNumber('id')->name('halls.restore');
        Route::get('/halls/{id}', [HallController::class, 'show'])->whereNumber('id')->name('halls.show');
        Route::get('/halls/{id}/edit', [HallController::class, 'edit'])->whereNumber('id')->name('halls.edit');
        Route::put('/halls/{id}', [HallController::class, 'update'])->whereNumber('id')->name('halls.update');
        Route::delete('/halls/{id}', [HallController::class, 'destroy'])->whereNumber('id')->name('halls.destroy');
    });

    // Staff: dynamic (database-backed via StaffController). Tenant-scoped, never for the SaaS admin.
    Route::middleware('module:staff')->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/archived', [StaffController::class, 'archived'])->name('staff.archived');
        Route::post('/staff/{id}/restore', [StaffController::class, 'restore'])->whereNumber('id')->name('staff.restore');
        Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->whereNumber('id')->name('staff.edit');
        Route::put('/staff/{id}', [StaffController::class, 'update'])->whereNumber('id')->name('staff.update');
        Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->whereNumber('id')->name('staff.destroy');
    });

    // Students: dynamic (database-backed via StudentController). Tenant-scoped, never for the SaaS admin.
    Route::middleware('module:students')->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/archived', [StudentController::class, 'archived'])->name('students.archived');
        Route::post('/students/{id}/restore', [StudentController::class, 'restore'])->whereNumber('id')->name('students.restore');
        Route::get('/students/{id}', [StudentController::class, 'show'])->whereNumber('id')->name('students.show');
        Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->whereNumber('id')->name('students.edit');
        Route::put('/students/{id}', [StudentController::class, 'update'])->whereNumber('id')->name('students.update');
        Route::delete('/students/{id}', [StudentController::class, 'destroy'])->whereNumber('id')->name('students.destroy');
    });

    // Settings: tenant-scoped, never for the SaaS admin. Each sub-section is
    // its own module (fees / general / roles) so access can be granted separately.
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', '/settings/fee-structure');

        // Fee structure: dynamic (database-backed via FeeStructureController).
        Route::middleware('module:fees')->group(function () {
            Route::get('/fee-structure', [FeeStructureController::class, 'index'])->name('fees');
            Route::post('/fee-structure', [FeeStructureController::class, 'store'])->name('fees.store');
            Route::get('/fee-structure/archived', [FeeStructureController::class, 'archived'])->name('fees.archived');
            Route::post('/fee-structure/{id}/restore', [FeeStructureController::class, 'restore'])->whereNumber('id')->name('fees.restore');
            Route::get('/fee-structure/{id}/edit', [FeeStructureController::class, 'edit'])->whereNumber('id')->name('fees.edit');
            Route::put('/fee-structure/{id}', [FeeStructureController::class, 'update'])->whereNumber('id')->name('fees.update');
            Route::delete('/fee-structure/{id}', [FeeStructureController::class, 'destroy'])->whereNumber('id')->name('fees.destroy');
        });

        Route::middleware('module:general')->group(function () {
            Route::get('/general', [GeneralSettingController::class, 'index'])->name('general');
            Route::put('/general', [GeneralSettingController::class, 'update'])->name('general.update');
        });

        // Roles & Permissions: dynamic (database-backed via RoleController).
        Route::middleware('module:roles')->group(function () {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles');
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
            Route::put('/roles/{id}', [RoleController::class, 'update'])->whereNumber('id')->name('roles.update');
            Route::put('/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->whereNumber('id')->name('roles.permissions');
            Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->whereNumber('id')->name('roles.destroy');
        });
    });
});
