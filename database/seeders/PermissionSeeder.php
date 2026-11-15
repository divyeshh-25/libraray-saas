<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = ['branches', 'halls', 'staff', 'students', 'attendance', 'roles', 'general', 'fees'];
        $actions = ['view', 'add', 'update', 'delete'];
        $labels = ['roles' => 'Role & Permission', 'general' => 'General Settings', 'fees' => 'Fee Structure', 'attendance' => 'Student Attendance'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::query()->firstOrCreate(
                    ['name' => "{$module}.{$action}"],
                    ['module' => $module, 'action' => $action, 'description' => ucfirst($action).' access to the '.($labels[$module] ?? ucfirst($module)).' module.']
                );
            }
        }

        // Enrolling a student is managed from the Students module itself (no
        // standalone Enrollments page any more), so it's its own action there
        // rather than a whole separate module.
        Permission::query()->firstOrCreate(
            ['name' => 'students.enroll'],
            ['module' => 'students', 'action' => 'enroll', 'description' => 'Enroll students and edit their enrollments from the Students module.']
        );

        // Carry the ability forward: any role that could add enrollments under
        // the old standalone module keeps that ability via students.enroll.
        $oldEnrollAdd = Permission::where('name', 'enrollments.add')->first();
        if ($oldEnrollAdd) {
            $enrollPermission = Permission::where('name', 'students.enroll')->first();
            Role::whereHas('permissions', fn ($q) => $q->where('permission_id', $oldEnrollAdd->id))
                ->get()
                ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching([$enrollPermission->id => ['created_at' => now()]]));
        }

        // Replaced by the three granular settings modules above.
        Permission::where('module', 'settings')->delete();

        // Enrollments is no longer a standalone module — folded into students.enroll above.
        Permission::where('module', 'enrollments')->delete();
    }
}
