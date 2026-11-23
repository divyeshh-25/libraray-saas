<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\FeeInvoice;
use App\Models\FeeTransaction;
use App\Models\Hall;
use App\Models\Plan;
use App\Models\Seat;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Tenant;
use App\Models\TenantPlan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * One /dashboard URL, three staff-area dashboards chosen by who is signed in:
 * SaaS admin (admin guard), library owner, and librarian/staff. The student
 * dashboard lives in StudentPortalController.
 */
class DashboardController extends Controller
{
    use ScopesToViewer;

    public function __invoke(): View
    {
        if (Auth::guard('admin')->check()) {
            return $this->saasAdmin();
        }

        $user = Auth::guard('web')->user();

        return $user->user_type === 'owner' ? $this->owner($user) : $this->staff($user);
    }

    private function saasAdmin(): View
    {
        $tenantsByStatus = Tenant::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $revenueRows = TenantPlan::where('status', '!=', 'cancelled')
            ->where('starts_at', '>=', now()->startOfMonth()->subMonths(11))
            ->get(['amount', 'starts_at']);

        $revenue = $this->monthlyTotals($revenueRows, 'starts_at', 'amount', 12);
        $plans = Plan::withCount(['tenantPlans as tenants_count' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('price')->get();

        return view('dashboards.admin', [
            'tenantTotal' => $tenantsByStatus->sum(),
            'tenantsActive' => (int) ($tenantsByStatus['active'] ?? 0),
            'tenantsByStatus' => $tenantsByStatus,
            'activeSubscriptions' => TenantPlan::where('status', 'active')->count(),
            'plans' => $plans,
            'plansActive' => $plans->where('status', 'active')->count(),
            'revenue' => $revenue,
            'revenueTotal' => TenantPlan::where('status', '!=', 'cancelled')->sum('amount'),
            'revenueThisMonth' => (float) end($revenue),
            'attention' => TenantPlan::with(['tenant', 'plan'])
                ->where('status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addDays(14))
                ->orderBy('expires_at')->take(8)->get(),
            'recentTenants' => Tenant::latest()->take(5)->get(),
            'studentTotal' => Student::count(),
        ]);
    }

    private function owner(User $user): View
    {
        $branchIds = $this->scopeBranchRow(Branch::query())->pluck('id');
        $showFees = $user->canAccessModule('fees');

        $data = $this->operations($branchIds) + [
            'branches' => $this->scopeBranchRow(Branch::query())->withCount('halls')->orderBy('name')->get(),
            'hallCount' => $this->scopeThroughBranch(Hall::query())->count(),
            'showFees' => $showFees,
        ];

        if ($showFees) {
            $data += $this->feeFigures();
        }

        return view('dashboards.owner', $data);
    }

    private function staff(User $user): View
    {
        $branchIds = $this->scopeBranchRow(Branch::query())->pluck('id');
        $showFees = $user->canAccessModule('fees');

        $students = $this->scopeThroughUser(Student::query())
            ->where('status', 'active')
            ->with(['user', 'enrollments' => fn ($q) => $q->where('status', 'active')->with(['shift', 'seatAllocation.seat'])])
            ->latest()->get();

        $today = StudentAttendance::whereIn('branch_id', $branchIds)->whereDate('attendance_date', today())
            ->pluck('status', 'student_id');

        $halls = $this->scopeThroughBranch(Hall::query())->where('status', 'active')->with('branch')->orderBy('name')->get()
            ->map(function (Hall $hall) {
                $total = $hall->seatsCount();
                $occupied = Seat::where('status', 'active')
                    ->whereHas('floorPlanObject.floor', fn ($q) => $q->where('hall_id', $hall->id))
                    ->whereHas('allocations', fn ($q) => $q->where('status', 'active'))
                    ->count();

                return ['hall' => $hall, 'total' => $total, 'occupied' => $occupied, 'available' => max(0, $total - $occupied)];
            });

        $activities = collect([
            ['Admit a student', 'students.create', 'students', 'add'],
            ['Enroll a student', 'enrollments.create', 'students', 'enroll'],
            ['Mark attendance', 'attendance.index', 'attendance', 'add'],
            ['Collect fees', 'students.index', 'students', 'update'],
            ['View students', 'students.index', 'students', 'view'],
            ['View halls', 'halls.index', 'halls', 'view'],
        ])->filter(fn ($a) => $a[3] === 'view' ? $user->canAccessModule($a[2]) : $user->canDo($a[2], $a[3]))
            ->unique(fn ($a) => $a[1])->values();

        $data = $this->operations($branchIds) + [
            'students' => $students->take(10),
            'assignedCount' => $students->count(),
            'todayStatus' => $today,
            'halls' => $halls,
            'activities' => $activities,
            'showFees' => $showFees,
        ];

        if ($showFees) {
            $data['outstanding'] = $this->scopeThroughStudentUser(FeeInvoice::query())->outstanding()->sum('balance_amount');
        }

        return view('dashboards.staff', $data);
    }

    /**
     * Seat and attendance figures shared by the owner and staff dashboards.
     *
     * @param  Collection<int, int>  $branchIds
     * @return array<string, mixed>
     */
    private function operations(Collection $branchIds): array
    {
        $seats = Seat::whereIn('branch_id', $branchIds)->where('status', 'active');
        $seatTotal = (clone $seats)->count();
        $seatOccupied = (clone $seats)->whereHas('allocations', fn ($q) => $q->where('status', 'active'))->count();

        $attendance = StudentAttendance::whereIn('branch_id', $branchIds)->whereDate('attendance_date', today())
            ->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return [
            'seatTotal' => $seatTotal,
            'seatOccupied' => $seatOccupied,
            'seatAvailable' => max(0, $seatTotal - $seatOccupied),
            'studentCount' => $this->scopeThroughUser(Student::query())->where('status', 'active')->count(),
            'attendanceToday' => $attendance,
            'presentToday' => (int) (($attendance['present'] ?? 0) + ($attendance['late'] ?? 0)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function feeFigures(): array
    {
        $success = fn () => $this->scopeThroughStudentUser(FeeTransaction::query())->where('status', 'success');

        $rows = $success()->where('transaction_date', '>=', now()->startOfMonth()->subMonths(5))
            ->get(['amount', 'transaction_date']);

        return [
            'collectedThisMonth' => (float) $success()->where('transaction_date', '>=', now()->startOfMonth())->sum('amount'),
            'collectedTotal' => (float) $success()->sum('amount'),
            'outstanding' => (float) $this->scopeThroughStudentUser(FeeInvoice::query())->outstanding()->sum('balance_amount'),
            'collectionTrend' => $this->monthlyTotals($rows, 'transaction_date', 'amount', 6),
            'recentPayments' => $success()->with(['student.user', 'invoice'])->latest('transaction_date')->latest('id')->take(8)->get(),
        ];
    }

    /**
     * Sum a date/amount column into the last $months calendar months
     * (oldest first), with zeros for empty months.
     *
     * @return array<string, float>
     */
    private function monthlyTotals(Collection $rows, string $dateColumn, string $amountColumn, int $months): array
    {
        $buckets = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $buckets[now()->startOfMonth()->subMonths($i)->format('Y-m')] = 0.0;
        }

        foreach ($rows as $row) {
            $key = $row->{$dateColumn}->format('Y-m');
            if (isset($buckets[$key])) {
                $buckets[$key] += (float) $row->{$amountColumn};
            }
        }

        $labelled = [];
        foreach ($buckets as $key => $total) {
            $labelled[Carbon::createFromFormat('Y-m', $key)->format('M')] = $total;
        }

        return $labelled;
    }
}
