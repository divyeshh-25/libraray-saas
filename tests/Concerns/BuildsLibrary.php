<?php

namespace Tests\Concerns;

use App\Models\Branch;
use App\Models\FeeStructure;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;

trait BuildsLibrary
{
    protected function tenant(string $name = 'Lib A'): Tenant
    {
        return Tenant::create(['owner_name' => 'Owner', 'library_name' => $name, 'status' => 'active']);
    }

    protected function branch(Tenant $tenant, string $code = 'MAIN'): Branch
    {
        return Branch::create(['tenant_id' => $tenant->id, 'name' => "Branch {$code}", 'code' => $code, 'status' => 'active']);
    }

    protected function owner(Tenant $tenant, ?Branch $branch = null): User
    {
        return User::factory()->create(['tenant_id' => $tenant->id, 'branch_id' => $branch?->id, 'user_type' => 'owner']);
    }

    protected function student(Tenant $tenant, Branch $branch, string $code = 'STU1'): Student
    {
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'user_type' => 'student']);

        return Student::create(['user_id' => $user->id, 'student_code' => $code, 'status' => 'active']);
    }

    protected function shift(Branch $branch): Shift
    {
        return Shift::create(['branch_id' => $branch->id, 'name' => 'Morning', 'start_time' => '09:00', 'end_time' => '13:00', 'status' => 'active']);
    }

    protected function feePlan(Branch $branch, Shift $shift, int $amount = 1200): FeeStructure
    {
        return FeeStructure::create([
            'branch_id' => $branch->id, 'shift_id' => $shift->id, 'name' => 'Monthly Morning',
            'amount' => $amount, 'duration_type' => 'monthly', 'status' => 'active',
        ]);
    }

    /** Staff user holding exactly the given "module.action" permissions. */
    protected function staffWith(Tenant $tenant, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'user_type' => 'staff']);
        $staff = Staff::create(['user_id' => $user->id, 'status' => 'active']);
        $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Role '.uniqid()]);

        foreach ($permissions as $name) {
            [$module, $action] = explode('.', $name);
            $permission = Permission::where('name', $name)->first()
                ?? Permission::forceCreate(['name' => $name, 'module' => $module, 'action' => $action]);
            $role->permissions()->attach($permission->id, ['created_at' => now()]);
        }

        $staff->roles()->attach($role->id, ['created_at' => now()]);

        return $user;
    }

    /** @return array<string, mixed> enrollment payload for the given records */
    protected function enrollPayload(Student $student, Branch $branch, Shift $shift, FeeStructure $fee, array $extra = []): array
    {
        return $extra + [
            'student_id' => $student->id,
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'fee_structure_id' => $fee->id,
            'enrollment_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
        ];
    }
}
