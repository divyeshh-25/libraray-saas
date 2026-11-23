<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Plan;
use App\Models\StudentAttendance;
use App\Models\TenantPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLibrary;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use BuildsLibrary, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_saas_admin_dashboard_shows_platform_tenants_subscriptions_plans_and_revenue(): void
    {
        $admin = Admin::create(['name' => 'Root', 'email' => 'root@example.com', 'password' => 'secret123', 'status' => 'active']);
        $plan = Plan::create(['name' => 'Growth Plan', 'price' => 999, 'billing_cycle' => 'monthly', 'max_branches' => 3, 'max_students' => 300, 'status' => 'active']);
        $a = $this->tenant('Alpha Library');
        $this->tenant('Beta Library');
        TenantPlan::create(['tenant_id' => $a->id, 'plan_id' => $plan->id, 'starts_at' => now(), 'expires_at' => now()->addDays(5), 'amount' => 999, 'status' => 'active']);

        $this->actingAs($admin, 'admin')->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Active tenants')->assertSee('Active subscriptions')->assertSee('Revenue this month')
            ->assertSee('₹999')->assertSee('Growth Plan')->assertSee('Alpha Library');
    }

    public function test_owner_dashboard_shows_only_their_own_tenant_data(): void
    {
        $a = $this->tenant('Alpha Library');
        $branchA = $this->branch($a, 'A1');
        $owner = $this->owner($a);
        $studentA = $this->student($a, $branchA, 'A-STU');
        $shiftA = $this->shift($branchA);
        $planA = $this->feePlan($branchA, $shiftA, 1000);
        StudentAttendance::create(['student_id' => $studentA->id, 'branch_id' => $branchA->id, 'shift_id' => $shiftA->id, 'attendance_date' => today(), 'status' => 'present']);

        $b = $this->tenant('Beta Library');
        $branchB = $this->branch($b, 'B1');
        $ownerB = $this->owner($b);
        $studentB = $this->student($b, $branchB, 'B-STU');
        $this->student($b, $branchB, 'B-STU2');
        $planB = $this->feePlan($branchB, $this->shift($branchB), 777);

        $this->actingAs($owner)->postJson(route('enrollments.store'), $this->enrollPayload($studentA, $branchA, $shiftA, $planA, ['initial_payment' => 400, 'payment_method' => 'cash']))->assertOk();
        $this->actingAs($ownerB)->postJson(route('enrollments.store'), $this->enrollPayload($studentB, $branchB, $planB->shift, $planB, ['initial_payment' => 111, 'payment_method' => 'cash']))->assertOk();

        $response = $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $response->assertSee('Fees collected this month')->assertSee('₹400')->assertSee('₹600')->assertSee('Branch A1')
            ->assertDontSee('Branch B1')->assertDontSee('₹111')->assertDontSee('₹666');
        $this->assertSame(1, $response->viewData('studentCount'));
        $this->assertSame(1, $response->viewData('presentToday'));
        $this->assertSame(400.0, $response->viewData('collectedThisMonth'));
        $this->assertSame(600.0, $response->viewData('outstanding'));
    }

    public function test_staff_dashboard_shows_assigned_students_and_only_permitted_activities(): void
    {
        $tenant = $this->tenant();
        $branch = $this->branch($tenant);
        $otherBranch = $this->branch($tenant, 'OTHER');
        $mine = $this->student($tenant, $branch, 'MINE');
        $this->student($tenant, $otherBranch, 'THEIRS');

        $staff = $this->staffWith($tenant, $branch, ['students.view', 'attendance.view', 'attendance.add']);

        $response = $this->actingAs($staff)->get(route('dashboard'))->assertOk();
        $response->assertSee('Assigned students')->assertSee($mine->user->name)->assertSee('Mark attendance')->assertSee('View students')
            ->assertDontSee('Enroll a student')->assertDontSee('Collect fees')->assertDontSee('Outstanding fees');
        $this->assertSame(1, $response->viewData('assignedCount'));
    }

    public function test_student_dashboard_shows_profile_enrollment_fees_and_payment_history(): void
    {
        $tenant = $this->tenant();
        $branch = $this->branch($tenant);
        $owner = $this->owner($tenant);
        $student = $this->student($tenant, $branch);
        $shift = $this->shift($branch);
        $plan = $this->feePlan($branch, $shift, 900);
        $this->actingAs($owner)->postJson(route('enrollments.store'), $this->enrollPayload($student, $branch, $shift, $plan, ['initial_payment' => 300, 'payment_method' => 'upi']))->assertOk();

        $this->actingAs($student->user)->get(route('student.dashboard'))->assertOk()
            ->assertSee($student->user->name)->assertSee('Branch MAIN')->assertSee('Morning')->assertSee('Outstanding')
            ->assertSee('₹900')->assertSee('₹300')->assertSee('₹600')->assertSee('Upi');
    }

    public function test_dashboards_require_the_right_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect();
        $tenant = $this->tenant();
        $student = $this->student($tenant, $this->branch($tenant));
        $this->actingAs($student->user)->get(route('dashboard'))->assertForbidden();
    }
}
