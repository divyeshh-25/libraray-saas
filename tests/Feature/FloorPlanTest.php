<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Shift;
use App\Models\Student;
use App\Models\StudentSeatAllocation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloorPlanTest extends TestCase
{
    use RefreshDatabase;

    private function owner(Tenant $tenant, ?Branch $branch = null): User
    {
        return User::factory()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch?->id,
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

    public function test_designer_renders_an_empty_plan_for_a_new_hall(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $hall = $this->hall($branch);
        $user = $this->owner($tenant, $branch);

        $response = $this->actingAs($user, 'web')->get(route('halls.floor-plan', $hall->id));

        $response->assertOk();
        $response->assertSee('Library Floor Planner');

        $this->assertDatabaseHas('floors', ['hall_id' => $hall->id, 'floor_number' => 1]);
    }

    public function test_designer_exposes_seat_allocation_state_for_existing_seats(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $hall = $this->hall($branch);
        $owner = $this->owner($tenant, $branch);

        $this->actingAs($owner, 'web')->postJson(route('halls.floor-plan.save', $hall->id), [
            'floor' => ['name' => 'Ground Floor', 'width' => 1000, 'height' => 650],
            'objects' => [
                ['type' => 'SEAT', 'name' => 'S1', 'x' => 10, 'y' => 10, 'w' => 46, 'h' => 46, 'rotation' => 0, 'detail' => ''],
            ],
        ])->assertOk();

        $seat = Seat::where('seat_number', 'S1')->first();
        $studentUser = User::factory()->create(['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'user_type' => 'student']);
        $student = Student::create(['user_id' => $studentUser->id, 'student_code' => 'STU1', 'status' => 'active']);
        $shift = Shift::create(['branch_id' => $branch->id, 'name' => 'Morning', 'start_time' => '09:00', 'end_time' => '13:00', 'status' => 'active']);

        $this->actingAs($owner, 'web')->postJson(route('halls.seats.allocate', ['id' => $hall->id, 'seatId' => $seat->id]), [
            'student_id' => $student->id,
            'shift_id' => $shift->id,
            'start_date' => now()->toDateString(),
        ])->assertOk();

        $response = $this->actingAs($owner, 'web')->get(route('halls.floor-plan', $hall->id));

        $response->assertOk();
        $response->assertSee($studentUser->name, false);
        $response->assertSee('seatAllocSec', false);
    }

    public function test_saving_a_plan_persists_objects_and_creates_seats(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $hall = $this->hall($branch);
        $user = $this->owner($tenant, $branch);

        $payload = [
            'floor' => ['name' => 'Ground Floor', 'width' => 1000, 'height' => 650],
            'objects' => [
                ['type' => 'SEAT', 'name' => 'S1', 'x' => 10, 'y' => 10, 'w' => 46, 'h' => 46, 'rotation' => 0, 'detail' => ''],
                ['type' => 'WALL', 'name' => 'Wall', 'x' => 0, 'y' => 0, 'w' => 150, 'h' => 16, 'rotation' => 0, 'detail' => ''],
            ],
        ];

        $response = $this->actingAs($user, 'web')
            ->postJson(route('halls.floor-plan.save', $hall->id), $payload);

        $response->assertOk();

        $this->assertDatabaseHas('floor_plan_objects', ['title' => 'S1', 'type' => 'SEAT']);
        $this->assertDatabaseHas('seats', ['seat_number' => 'S1', 'branch_id' => $branch->id, 'status' => 'active']);
        $this->assertDatabaseCount('floor_plan_objects', 2);
    }

    public function test_removing_a_seat_with_an_active_allocation_cancels_it_instead_of_orphaning_it(): void
    {
        $tenant = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branch = $this->branch($tenant);
        $hall = $this->hall($branch);
        $user = $this->owner($tenant, $branch);

        $this->actingAs($user, 'web')->postJson(route('halls.floor-plan.save', $hall->id), [
            'floor' => ['name' => 'Ground Floor', 'width' => 1000, 'height' => 650],
            'objects' => [
                ['type' => 'SEAT', 'name' => 'S1', 'x' => 10, 'y' => 10, 'w' => 46, 'h' => 46, 'rotation' => 0, 'detail' => ''],
            ],
        ]);

        $seat = Seat::where('seat_number', 'S1')->first();
        $shift = Shift::create(['branch_id' => $branch->id, 'name' => 'Morning', 'start_time' => '09:00', 'end_time' => '13:00', 'status' => 'active']);
        $studentUser = User::factory()->create(['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'user_type' => 'student']);
        $student = Student::create(['user_id' => $studentUser->id, 'student_code' => 'STU1', 'status' => 'active']);

        $allocation = StudentSeatAllocation::create([
            'student_id' => $student->id,
            'seat_id' => $seat->id,
            'shift_id' => $shift->id,
            'start_date' => now(),
            'status' => 'active',
        ]);

        // Save a plan with no objects at all: the seat is removed.
        $this->actingAs($user, 'web')->postJson(route('halls.floor-plan.save', $hall->id), [
            'floor' => ['name' => 'Ground Floor', 'width' => 1000, 'height' => 650],
            'objects' => [],
        ])->assertOk();

        $this->assertDatabaseHas('seats', ['id' => $seat->id, 'status' => 'retired']);
        $this->assertDatabaseHas('student_seat_allocations', ['id' => $allocation->id, 'status' => 'cancelled']);
    }

    public function test_a_user_from_another_tenant_cannot_view_or_save_the_plan(): void
    {
        $tenantA = Tenant::create(['owner_name' => 'A', 'library_name' => 'Lib A', 'status' => 'active']);
        $branchA = $this->branch($tenantA);
        $hall = $this->hall($branchA);

        $tenantB = Tenant::create(['owner_name' => 'B', 'library_name' => 'Lib B', 'status' => 'active']);
        $branchB = $this->branch($tenantB);
        $intruder = $this->owner($tenantB, $branchB);

        $this->actingAs($intruder, 'web')->get(route('halls.floor-plan', $hall->id))->assertNotFound();

        $this->actingAs($intruder, 'web')->postJson(route('halls.floor-plan.save', $hall->id), [
            'floor' => ['name' => 'X', 'width' => 1000, 'height' => 650],
            'objects' => [],
        ])->assertNotFound();
    }
}
