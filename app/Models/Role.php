<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['tenant_id', 'name', 'description', 'is_system'])]
class Role extends Model
{
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'staff_roles')->withPivot('created_at');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')->withPivot('created_at');
    }

    /**
     * The default roles every tenant gets, and the module/action permissions
     * each starts out with. The role itself (name, existence) is locked, but
     * these starting permissions can still be freely changed afterwards.
     *
     * @return array<string, array<int, array<int, string>>>
     */
    public static function defaultModuleGrants(): array
    {
        return [
            'Admin' => [
                'branches' => ['view', 'add', 'update', 'delete'],
                'halls' => ['view', 'add', 'update', 'delete'],
                'staff' => ['view', 'add', 'update', 'delete'],
                'students' => ['view', 'add', 'update', 'delete'],
                'roles' => ['view', 'add', 'update', 'delete'],
                'general' => ['view', 'add', 'update', 'delete'],
                'fees' => ['view', 'add', 'update', 'delete'],
            ],
            'Librarian' => [
                'branches' => ['view'],
                'halls' => ['view', 'add', 'update'],
                'students' => ['view', 'add', 'update'],
            ],
            'Accountant' => [
                'students' => ['view'],
                'fees' => ['view', 'add', 'update'],
                'general' => ['view'],
            ],
        ];
    }

    /**
     * Ensure the default roles exist as global, system-level roles (tenant_id
     * is null) shared by every tenant, rather than duplicated per tenant.
     *
     * @return \Illuminate\Support\Collection<string, Role>
     */
    public static function ensureDefaultsExist()
    {
        $roles = collect(self::defaultModuleGrants())->map(
            fn (array $modules, string $name) => self::query()->updateOrCreate(
                ['tenant_id' => null, 'name' => $name],
                ['is_system' => true]
            )
        );

        $roles->each(function (Role $role, string $name) {
            $names = collect(self::defaultModuleGrants()[$name])
                ->flatMap(fn (array $actions, string $module) => collect($actions)->map(fn ($action) => "{$module}.{$action}"));

            $permissionIds = Permission::query()->whereIn('name', $names)->pluck('id');
            $role->permissions()->syncWithoutDetaching(
                $permissionIds->mapWithKeys(fn ($id) => [$id => ['created_at' => now()]])
            );
        });

        return $roles;
    }
}
