<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['email' => 'owner@demolibrary.test'],
            [
                'owner_name' => 'Demo Owner',
                'library_name' => 'Demo Library',
                'phone' => '9999999999',
                'status' => 'active',
            ]
        );

        $branches = [
            ['name' => 'Main Branch', 'code' => 'MAIN'],
            ['name' => 'City Branch', 'code' => 'CITY'],
        ];

        $roles = collect(['Admin', 'Librarian', 'Accountant'])
            ->mapWithKeys(fn (string $name) => [
                $name => Role::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $name],
                ),
            ]);

        $roleNames = ['Admin', 'Librarian', 'Accountant'];

        foreach ($branches as $branchData) {
            $branch = Branch::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $branchData['code']],
                ['name' => $branchData['name'], 'status' => 'active'],
            );

            foreach ($roleNames as $index => $roleName) {
                $employeeCode = $branchData['code'].'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

                $user = User::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'email' => strtolower($branchData['code'].'.'.$roleName).'@demolibrary.test'],
                    [
                        'branch_id' => $branch->id,
                        'name' => $branchData['name'].' '.$roleName,
                        'phone' => '9000000000',
                        'password' => 'password',
                        'user_type' => 'staff',
                        'status' => 'active',
                    ],
                );

                $staff = Staff::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['employee_code' => $employeeCode, 'designation' => $roleName, 'status' => 'active'],
                );

                $role = $roles[$roleName];
                $staff->roles()->syncWithoutDetaching([$role->id => ['created_at' => now()]]);
            }
        }
    }
}
