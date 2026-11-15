<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Shift;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatAllocationTest extends TestCase
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

    private function branch(Tenant $tenant): Branch
    {
        return Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'status' => 'active',
        ]);
    }

    private function hall(Branch $branch): Hall
    {
        return Hall::create([
            'branch_id' => $branch->id,
            'name' => 'Reading Hall',
            'status' => 'active',
            'floors' => 1,
        ]);
    }

    private function student(Tenant $tenant, Branch $branch, string $code = 'STU1'): Student
    {
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'user_type' => 'student']);

        return Student::create(['user_id' => $user->id, 'student_code' => $code, 'status' => 'active']);
    }

    private function seatViaDesigner(User $owner, Hall $hall, string $seatNumber = 'S1'): Seat
    {
        $this->actingAs($owner, 'web')->postJson(route('halls.floor-plan.save', $hall->id), [
            'floor' => ['name' => 'Ground Floor', 'width' => 1000, 'height' => 650],
            'objects' => [
                ['type' => 'SEAT', 'name' => $seatNumber, 'x' => 10, 'y' => 10, 'w' => 46, 'h' => 46, 'rotation' => 0, 'detail' => ''],
            ],
        ])->assertOk();

        return Seat::where('seat_number', $seatNumber)->first();
    }

    public function test_an_available_seat_can_be_allocated_to_a_student(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $hall = $this->hall($branch);
        $owner = $this->owner($tenant, $branch);
        $seat = $this->seatViaDesigner($owner, $hall);
        $student = $this->student($tenant, $branch);
        $shift = Shift::create(['branch_id' => $branch->id, 'name' => 'Morning', 'start_time' => '09:00', 'end_time' => '13:00', 'status' => 'active']);

        $response = $this->actingAs($owner, 'web')->postJson(route('halls.seats.allocate', ['id' => $hall->id, 'seatId' => $seat->id]), [
            'student_id' => $student->id,
            'shift_id' => $shift->id,
            'start_date' => now()->toDateString(),
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('student_seat_allocations', [
            'student_id' => $student->id,
            'seat_id' => $seat->id,
            'status' => 'active',
        ]);
    }

    public function test_an_already_allocated_seat_cannot_be_double_booked(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $hall = $this->hall($branch);
        $owner = $this->owner($tenant, $branch);
        $seat = $this->seatViaDesigner($owner, $hall);
        $studentA = $this->student($tenant, $branch, 'STU1');
        $studentB = $this->student($tenant, $branch, 'STU2');
        $shift = Shift::create(['branch_id' => $branch->id, 'name' => 'Morning', 'start_time' => '09:00', 'end_time' => '13:00', 'status' => 'active']);

        $this->actingAs($owner, 'web')->postJson(route('halls.seats.allocate', ['id' => $hall->id, 'seatId' => $seat->id]), [
            'student_id' => $studentA->id,
            'shift_id' => $shift->id,
            'start_date' => now()->toDateString(),
        ])->assertOk();

        $this->actingAs($owner, 'web')->postJson(route('halls.seats.allocate', ['id' => $hall->id, 'seatId' => $seat->id]), [
            'student_id' => $studentB->id,
            'shift_id' => $shift->id,
            'start_date' => now()->toDateString(),
        ])->assertStatus(422);
    }

    public function test_releasing_a_seat_frees_it_for_reallocation(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $hall = $this->hall($branch);
        $owner = $this->owner($tenant, $branch);
        $seat = $this->seatViaDesigner($owner, $hall);
        $student = $this->student($tenant, $branch);
        $shift = Shift::create(['branch_id' => $branch->id, 'name' => 'Morning', 'start_time' => '09:00', 'end_time' => '13:00', 'status' => 'active']);

        $this->actingAs($owner, 'web')->postJson(route('halls.seats.allocate', ['id' => $hall->id, 'seatId' => $seat->id]), [
            'student_id' => $student->id,
            'shift_id' => $shift->id,
            'start_date' => now()->toDateString(),
        ])->assertOk();

        $allocation = $seat->allocations()->where('status', 'active')->first();

        $this->actingAs($owner, 'web')->postJson(route('seat-allocations.release', $allocation->id))->assertOk();

        $this->assertDatabaseHas('student_seat_allocations', ['id' => $allocation->id, 'status' => 'cancelled']);

        $studentB = $this->student($tenant, $branch, 'STU2');
        $this->actingAs($owner, 'web')->postJson(route('halls.seats.allocate', ['id' => $hall->id, 'seatId' => $seat->id]), [
            'student_id' => $studentB->id,
            'shift_id' => $shift->id,
            'start_date' => now()->toDateString(),
        ])->assertOk();
    }

    public function test_hall_page_seeds_default_shifts_for_a_branch_that_has_none(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $hall = $this->hall($branch);
        $owner = $this->owner($tenant, $branch);

        $this->assertDatabaseCount('shifts', 0);

        $this->actingAs($owner, 'web')->get(route('halls.show', $hall->id))->assertOk();

        $this->assertDatabaseCount('shifts', 4);
        $this->assertDatabaseHas('shifts', ['branch_id' => $branch->id, 'name' => 'Morning']);
    }

    public function test_a_user_from_another_tenant_cannot_allocate_or_release_a_seat(): void
    {
        $tenantA = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branchA = $this->branch($tenantA);
        $hall = $this->hall($branchA);
        $ownerA = $this->owner($tenantA, $branchA);
        $seat = $this->seatViaDesigner($ownerA, $hall);
        $student = $this->student($tenantA, $branchA);
        $shift = Shift::create(['branch_id' => $branchA->id, 'name' => 'Morning', 'start_time' => '09:00', 'end_time' => '13:00', 'status' => 'active']);

        $this->actingAs($ownerA, 'web')->postJson(route('halls.seats.allocate', ['id' => $hall->id, 'seatId' => $seat->id]), [
            'student_id' => $student->id,
            'shift_id' => $shift->id,
            'start_date' => now()->toDateString(),
        ])->assertOk();

        $allocation = $seat->allocations()->where('status', 'active')->first();

        $tenantB = Tenant::create(['owner_name' => 'B', 'library_name' => 'Lib B', 'status' => 'active']);
        $branchB = $this->branch($tenantB);
        $intruder = $this->owner($tenantB, $branchB);

        $this->actingAs($intruder, 'web')
            ->postJson(route('halls.seats.allocate', ['id' => $hall->id, 'seatId' => $seat->id]), [
                'student_id' => $student->id,
                'shift_id' => $shift->id,
                'start_date' => now()->toDateString(),
            ])->assertNotFound();

        $this->actingAs($intruder, 'web')->postJson(route('seat-allocations.release', $allocation->id))->assertNotFound();
    }
}
