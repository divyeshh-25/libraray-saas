<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = ['branches', 'halls', 'staff', 'students', 'roles', 'general', 'fees'];
        $actions = ['view', 'add', 'update', 'delete'];
        $labels = ['roles' => 'Role & Permission', 'general' => 'General Settings', 'fees' => 'Fee Structure'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::query()->firstOrCreate(
                    ['name' => "{$module}.{$action}"],
                    ['module' => $module, 'action' => $action, 'description' => ucfirst($action).' access to the '.($labels[$module] ?? ucfirst($module)).' module.']
                );
            }
        }

        // Replaced by the three granular settings modules above.
        Permission::where('module', 'settings')->delete();
    }
}
