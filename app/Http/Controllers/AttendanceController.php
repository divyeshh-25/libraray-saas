<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\Shift;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    use ScopesToViewer;

    public function index(Request $request): View
    {
        $branches = $this->scopeBranchRow(Branch::query())->orderBy('name')->get();
        $branch = $this->resolveSelectedBranch($request, $branches);
        $date = $request->date('date') ?? today();
        $shiftId = $request->integer('shift_id') ?: null;

        $shifts = $branch ? Shift::where('branch_id', $branch->id)->orderBy('name')->get() : collect();

        $enrollments = collect();
        if ($branch) {
            $enrollments = StudentEnrollment::where('branch_id', $branch->id)
                ->where('status', 'active')
                ->when($shiftId, fn ($q) => $q->where('shift_id', $shiftId))
                ->with(['student.user', 'shift', 'seatAllocation.seat'])
                ->get();
        }

        $marked = StudentAttendance::where('branch_id', $branch?->id)
            ->whereDate('attendance_date', $date)
            ->when($shiftId, fn ($q) => $q->where('shift_id', $shiftId))
            ->get()
            ->keyBy(fn (StudentAttendance $a) => "{$a->student_id}:{$a->shift_id}");

        return view('attendance.index', [
            'branches' => $branches,
            'shifts' => $shifts,
            'enrollments' => $enrollments,
            'marked' => $marked,
            'selectedBranch' => $branch,
            'selectedShiftId' => $shiftId,
            'selectedDate' => $date,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer'],
            'attendance_date' => ['required', 'date'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer'],
            'records.*.shift_id' => ['required', 'integer'],
            'records.*.status' => ['required', 'string', 'in:present,absent,late'],
        ]);

        $branch = $this->scopeBranchRow(Branch::where('id', $data['branch_id']))->firstOrFail();

        $enrolledPairs = StudentEnrollment::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->get(['student_id', 'shift_id'])
            ->map(fn (StudentEnrollment $e) => "{$e->student_id}:{$e->shift_id}")
            ->all();

        DB::transaction(function () use ($data, $branch, $enrolledPairs) {
            foreach ($data['records'] as $record) {
                abort_unless(in_array("{$record['student_id']}:{$record['shift_id']}", $enrolledPairs, true), 422, 'Student is not actively enrolled in this branch and shift.');

                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'shift_id' => $record['shift_id'],
                        'attendance_date' => $data['attendance_date'],
                    ],
                    [
                        'branch_id' => $branch->id,
                        'status' => $record['status'],
                        'marked_by' => Auth::id(),
                    ]
                );
            }
        });

        return redirect()->route('attendance.index', array_filter([
            'branch_id' => $branch->id,
            'shift_id' => $request->integer('shift_id') ?: null,
            'date' => $data['attendance_date'],
        ]))->with('success', 'Attendance saved.');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Branch>  $branches
     */
    private function resolveSelectedBranch(Request $request, $branches): ?Branch
    {
        $requestedId = $request->integer('branch_id') ?: null;

        if ($requestedId) {
            return $branches->firstWhere('id', $requestedId);
        }

        return $branches->first();
    }
}
