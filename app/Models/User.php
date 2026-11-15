<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['tenant_id', 'branch_id', 'name', 'email', 'phone', 'password', 'user_type', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * All "{module}.{action}" permission names granted to this user through
     * their assigned role(s). Owners aren't role-driven (they implicitly have
     * every permission); this is only meaningful for staff.
     *
     * @return array<int, string>
     */
    public function grantedPermissionNames(): array
    {
        if ($this->user_type !== 'staff') {
            return [];
        }

        return $this->staff
            ?->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->values()
            ->all() ?? [];
    }

    /**
     * The modules this user can see in the dashboard (i.e. has "view" on).
     *
     * Owners get everything; staff are limited to whatever their assigned
     * role(s) grant; students get none of the operator modules.
     *
     * @return array<int, string>
     */
    public function allowedModules(): array
    {
        if ($this->user_type === 'owner') {
            return ['branches', 'halls', 'staff', 'students', 'attendance', 'roles', 'general', 'fees'];
        }

        if ($this->user_type === 'staff') {
            return collect($this->grantedPermissionNames())
                ->filter(fn ($name) => str_ends_with($name, '.view'))
                ->map(fn ($name) => str($name)->before('.view')->toString())
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }

    public function canAccessModule(string $module): bool
    {
        return in_array($module, $this->allowedModules(), true);
    }

    /**
     * Check a specific action on a module, e.g. canDo('students', 'delete').
     * Owners can do everything within their tenant's modules; staff are
     * governed by their role's granted permissions.
     */
    public function canDo(string $module, string $action): bool
    {
        if ($this->user_type === 'owner') {
            return $this->canAccessModule($module);
        }

        if ($this->user_type === 'staff') {
            return in_array("{$module}.{$action}", $this->grantedPermissionNames(), true);
        }

        return false;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
