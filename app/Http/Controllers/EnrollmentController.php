<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\FeeStructure;
use App\Models\Seat;
use App\Models\Shift;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentSeatAllocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    use ScopesToViewer;

    public function create(Request $request): View
    {
        $presetStudent = $request->filled('student')
            ? $this->scopeThroughUser(Student::query())->with('user')->find($request->integer('student'))
            : null;

        $data = ['mode' => 'create', 'presetStudent' => $presetStudent] + $this->formOptions();

        return view($request->ajax() ? 'enrollments._form' : 'enrollments.form', $data);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validateData($request, null);

        $enrollment = DB::transaction(function () use ($data) {
            $student = $this->resolveStudent($data['student_id']);
            $branch = $this->resolveBranch($data['branch_id']);
            $shift = $this->resolveShift($branch->id, $data['shift_id']);
            $feeStructure = $this->resolveFeeStructure($branch->id, $data['fee_structure_id']);

            abort_if(
                StudentEnrollment::where('student_id', $student->id)->where('status', 'active')->exists(),
                422,
                'This student already has an active enrollment.'
            );

            $seatAllocationId = $this->allocateSeatIfRequested($data, $student, $branch, $shift);

            return StudentEnrollment::create([
                'student_id' => $student->id,
                'branch_id' => $branch->id,
                'shift_id' => $shift->id,
                'fee_structure_id' => $feeStructure->id,
                'seat_allocation_id' => $seatAllocationId,
                'enrollment_date' => $data['enrollment_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'status' => 'active',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Student enrolled.', 'enrollment' => $enrollment]);
        }

        return redirect()->route('students.index')->with('success', 'Student enrolled.');
    }

    public function edit(Request $request, int $id): View
    {
        $enrollment = $this->scopeThroughBranch(StudentEnrollment::query())
            ->with(['student.user', 'branch', 'shift', 'feeStructure'])
            ->findOrFail($id);

        $data = ['mode' => 'edit', 'enrollment' => $enrollment, 'presetStudent' => null] + $this->formOptions();

        return view($request->ajax() ? 'enrollments._form' : 'enrollments.form', $data);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $enrollment = $this->scopeThroughBranch(StudentEnrollment::query())->findOrFail($id);
        $data = $this->validateData($request, $enrollment);

        DB::transaction(function () use ($enrollment, $data) {
            $branch = $this->resolveBranch($data['branch_id']);
            $shift = $this->resolveShift($branch->id, $data['shift_id']);
            $feeStructure = $this->resolveFeeStructure($branch->id, $data['fee_structure_id']);
            $status = strtolower($data['status']);

            if ($status === 'active') {
                abort_if(
                    StudentEnrollment::where('student_id', $enrollment->student_id)
                        ->where('status', 'active')
                        ->where('id', '!=', $enrollment->id)
                        ->exists(),
                    422,
                    'This student already has another active enrollment.'
                );
            }

            $enrollment->update([
                'branch_id' => $branch->id,
                'shift_id' => $shift->id,
                'fee_structure_id' => $feeStructure->id,
                'enrollment_date' => $data['enrollment_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'status' => $status,
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Enrollment updated.']);
        }

        return redirect()->route('students.index')->with('success', 'Enrollment updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $branchIds = $this->scopeBranchRow(Branch::query())->pluck('id');

        return [
            'students' => $this->scopeThroughUser(Student::query())->with('user')->orderBy('student_code')->get(),
            'branches' => $this->scopeBranchRow(Branch::query())->orderBy('name')->get(),
            'shifts' => Shift::whereIn('branch_id', $branchIds)->orderBy('name')->get(),
            'feeStructures' => FeeStructure::whereIn('branch_id', $branchIds)->orderBy('name')->get(),
            'seats' => Seat::whereIn('branch_id', $branchIds)->where('status', 'active')
                ->with(['allocations' => fn ($q) => $q->where('status', 'active')])
                ->get()
                ->sortBy(fn (Seat $seat) => $seat->seat_number, SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
        ];
    }

    private function resolveStudent(int $id): Student
    {
        return $this->scopeThroughUser(Student::query())->findOrFail($id);
    }

    private function resolveBranch(int $id): Branch
    {
        return $this->scopeBranchRow(Branch::where('id', $id))->firstOrFail();
    }

    private function resolveShift(int $branchId, int $id): Shift
    {
        return Shift::where('branch_id', $branchId)->where('id', $id)->firstOrFail();
    }

    private function resolveFeeStructure(int $branchId, int $id): FeeStructure
    {
        return FeeStructure::where('branch_id', $branchId)->where('id', $id)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function allocateSeatIfRequested(array $data, Student $student, Branch $branch, Shift $shift): ?int
    {
        if (empty($data['seat_id'])) {
            return null;
        }

        $seat = Seat::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->where('id', $data['seat_id'])
            ->firstOrFail();

        abort_if($seat->allocations()->where('status', 'active')->exists(), 422, 'This seat is already allocated.');
        abort_if(
            StudentSeatAllocation::where('student_id', $student->id)->where('status', 'active')->exists(),
            422,
            'This student already has an active seat allocation.'
        );

        return StudentSeatAllocation::create([
            'student_id' => $student->id,
            'seat_id' => $seat->id,
            'shift_id' => $shift->id,
            'start_date' => $data['start_date'],
            'status' => 'active',
            'allocated_by' => Auth::id(),
        ])->id;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?StudentEnrollment $enrollment): array
    {
        $rules = [
            'branch_id' => ['required', 'integer'],
            'shift_id' => ['required', 'integer'],
            'fee_structure_id' => ['required', 'integer'],
            'enrollment_date' => ['required', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', 'in:Active,Completed,Cancelled'],
        ];

        if ($enrollment === null) {
            $rules['student_id'] = ['required', 'integer'];
            $rules['seat_id'] = ['nullable', 'integer'];
            unset($rules['status']);
        }

        return $request->validate($rules);
    }
}
