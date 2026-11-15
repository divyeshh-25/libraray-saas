<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\FeeStructure;
use App\Models\Hall;
use App\Models\Shift;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function owner(Tenant $tenant, Branch $branch): User
    {
        return User::factory()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'user_type' => 'owner',
        ]);
    }

    private function branch(Tenant $tenant, string $code = 'MAIN'): Branch
    {
        return Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Branch',
            'code' => $code,
            'status' => 'active',
        ]);
    }

    private function student(Tenant $tenant, Branch $branch, string $code = 'STU1'): Student
    {
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'user_type' => 'student']);

        return Student::create(['user_id' => $user->id, 'student_code' => $code, 'status' => 'active']);
    }

    private function shift(Branch $branch): Shift
    {
        return Shift::create(['branch_id' => $branch->id, 'name' => 'Morning', 'start_time' => '09:00', 'end_time' => '13:00', 'status' => 'active']);
    }

    private function feeStructure(Branch $branch, Shift $shift): FeeStructure
    {
        return FeeStructure::create([
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'name' => 'Monthly Morning',
            'amount' => 1200,
            'duration_type' => 'monthly',
            'status' => 'active',
        ]);
    }

    public function test_an_owner_can_create_an_enrollment(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $owner = $this->owner($tenant, $branch);
        $student = $this->student($tenant, $branch);
        $shift = $this->shift($branch);
        $fee = $this->feeStructure($branch, $shift);

        $response = $this->actingAs($owner, 'web')->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'fee_structure_id' => $fee->id,
            'enrollment_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('enrollments.index'));
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'fee_structure_id' => $fee->id,
            'status' => 'active',
        ]);
    }

    public function test_a_student_cannot_have_two_active_enrollments(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $owner = $this->owner($tenant, $branch);
        $student = $this->student($tenant, $branch);
        $shift = $this->shift($branch);
        $fee = $this->feeStructure($branch, $shift);

        $payload = [
            'student_id' => $student->id,
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'fee_structure_id' => $fee->id,
            'enrollment_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
        ];

        $this->actingAs($owner, 'web')->post(route('enrollments.store'), $payload)->assertRedirect(route('enrollments.index'));

        $this->actingAs($owner, 'web')->post(route('enrollments.store'), $payload)->assertStatus(422);

        $this->assertDatabaseCount('student_enrollments', 1);
    }

    public function test_an_enrollment_can_allocate_a_seat_atomically(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $owner = $this->owner($tenant, $branch);
        $student = $this->student($tenant, $branch);
        $shift = $this->shift($branch);
        $fee = $this->feeStructure($branch, $shift);

        $hall = Hall::create(['branch_id' => $branch->id, 'name' => 'Reading Hall', 'status' => 'active', 'floors' => 1]);
        $this->actingAs($owner, 'web')->postJson(route('halls.floor-plan.save', $hall->id), [
            'floor' => ['name' => 'Ground Floor', 'width' => 1000, 'height' => 650],
            'objects' => [
                ['type' => 'SEAT', 'name' => 'S1', 'x' => 10, 'y' => 10, 'w' => 46, 'h' => 46, 'rotation' => 0, 'detail' => ''],
            ],
        ])->assertOk();
        $seat = \App\Models\Seat::where('seat_number', 'S1')->first();

        $this->actingAs($owner, 'web')->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'fee_structure_id' => $fee->id,
            'seat_id' => $seat->id,
            'enrollment_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
        ])->assertRedirect(route('enrollments.index'));

        $this->assertDatabaseHas('student_seat_allocations', [
            'student_id' => $student->id,
            'seat_id' => $seat->id,
            'status' => 'active',
        ]);

        $enrollment = StudentEnrollment::first();
        $this->assertNotNull($enrollment->seat_allocation_id);
    }

    public function test_a_user_from_another_tenant_cannot_view_or_create_enrollments_for_a_different_tenant(): void
    {
        $tenantA = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branchA = $this->branch($tenantA);
        $studentA = $this->student($tenantA, $branchA);
        $shiftA = $this->shift($branchA);
        $feeA = $this->feeStructure($branchA, $shiftA);

        $tenantB = Tenant::create(['owner_name' => 'B', 'library_name' => 'Lib B', 'status' => 'active']);
        $branchB = $this->branch($tenantB, 'BMAIN');
        $intruder = $this->owner($tenantB, $branchB);

        $response = $this->actingAs($intruder, 'web')->post(route('enrollments.store'), [
            'student_id' => $studentA->id,
            'branch_id' => $branchA->id,
            'shift_id' => $shiftA->id,
            'fee_structure_id' => $feeA->id,
            'enrollment_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
        ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('student_enrollments', 0);
    }

    public function test_staff_without_the_enrollments_module_cannot_access_it(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);

        $staffUser = User::factory()->create(['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'user_type' => 'staff']);
        \App\Models\Staff::create(['user_id' => $staffUser->id, 'status' => 'active']);

        $this->actingAs($staffUser, 'web')->get(route('enrollments.index'))->assertForbidden();
    }
}
