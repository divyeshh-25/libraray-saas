<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Builds a demo tenant end to end the same way the real flows do:
 * - Tenant + owner, as created by Auth\RegisterController@register.
 * - Branches + staff (Librarian, Accountant), as created by TenantSeeder.
 * - Students, as created by StudentController@store, each logged to
 *   ActivityLog (module "students") the same way tenant onboarding is
 *   logged by TenantController@store.
 */
class DemoSeeder extends Seeder
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

        $owner = User::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'owner@demolibrary.test'],
            [
                'name' => 'Demo Owner',
                'phone' => '9999999999',
                'password' => 'password',
                'user_type' => 'owner',
                'status' => 'active',
            ],
        );

        $roles = Role::ensureDefaultsExist();

        $branches = [
            ['name' => 'Main Branch', 'code' => 'MAIN', 'students' => 20],
            ['name' => 'City Branch', 'code' => 'CITY', 'students' => 30],
        ];

        $staffRoles = ['Librarian', 'Accountant'];

        foreach ($branches as $branchData) {
            $branch = Branch::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $branchData['code']],
                ['name' => $branchData['name'], 'status' => 'active'],
            );

            Shift::seedDefaultsForBranch($branch->id);

            foreach ($staffRoles as $index => $roleName) {
                $employeeCode = $branchData['code'].'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

                $staffUser = User::query()->firstOrCreate(
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
                    ['user_id' => $staffUser->id],
                    ['employee_code' => $employeeCode, 'designation' => $roleName, 'status' => 'active'],
                );

                $staff->roles()->syncWithoutDetaching([$roles[$roleName]->id => ['created_at' => now()]]);
            }

            for ($i = 1; $i <= $branchData['students']; $i++) {
                $studentNumber = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $email = strtolower($branchData['code']).".student{$studentNumber}@demolibrary.test";

                $studentUser = User::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'email' => $email],
                    [
                        'branch_id' => $branch->id,
                        'name' => $branchData['name']." Student {$studentNumber}",
                        'phone' => '90000'.str_pad((string) ($i + ($branchData['code'] === 'MAIN' ? 0 : 1000)), 5, '0', STR_PAD_LEFT),
                        'password' => 'password',
                        'user_type' => 'student',
                        'status' => 'active',
                    ],
                );

                $student = Student::query()->firstOrCreate(
                    ['user_id' => $studentUser->id],
                    [
                        'student_code' => 'STU-'.now()->format('ymd').'-'.$branchData['code'].$studentNumber,
                        'admission_date' => now()->subDays(random_int(1, 180)),
                        'date_of_birth' => now()->subYears(random_int(18, 28)),
                        'address' => $branchData['name'].' locality, Demo City',
                        'guardian_name' => 'Guardian of '.$studentUser->name,
                        'guardian_phone' => '91000'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                        'status' => 'active',
                    ],
                );

                ActivityLog::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'user_id' => $owner->id,
                    'action' => 'created',
                    'module' => 'students',
                    'description' => "Student {$studentUser->name} registered.",
                    'ip_address' => null,
                ]);
            }
        }
    }
}
