<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Shift;
use App\Models\Student;
use App\Models\StudentSeatAllocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeatAllocationController extends Controller
{
    use ScopesToViewer;

    public function store(Request $request, int $hallId, int $seatId): JsonResponse
    {
        $hall = $this->scopeThroughBranch(Hall::query())->findOrFail($hallId);

        $seat = Seat::where('branch_id', $hall->branch_id)
            ->where('status', 'active')
            ->whereHas('floorPlanObject.floor', fn ($q) => $q->where('hall_id', $hall->id))
            ->findOrFail($seatId);

        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'shift_id' => ['required', 'integer'],
            'start_date' => ['required', 'date'],
        ]);

        $student = Student::whereHas('user', fn ($q) => $q->where('branch_id', $hall->branch_id))
            ->findOrFail($data['student_id']);

        $shift = Shift::where('branch_id', $hall->branch_id)->findOrFail($data['shift_id']);

        abort_if($seat->allocations()->where('status', 'active')->exists(), 422, 'This seat is already allocated.');
        abort_if(StudentSeatAllocation::where('student_id', $student->id)->where('status', 'active')->exists(), 422, 'This student already has an active seat allocation.');

        $allocation = DB::transaction(fn () => StudentSeatAllocation::create([
            'student_id' => $student->id,
            'seat_id' => $seat->id,
            'shift_id' => $shift->id,
            'start_date' => $data['start_date'],
            'status' => 'active',
            'allocated_by' => Auth::id(),
        ]));

        return response()->json([
            'message' => 'Seat allocated.',
            'allocation_id' => $allocation->id,
            'student_name' => $student->user->name,
            'shift_name' => $shift->name,
        ]);
    }

    public function release(int $allocationId): JsonResponse
    {
        $allocation = StudentSeatAllocation::with('seat')->findOrFail($allocationId);

        abort_unless(
            $this->scopeBranchRow(Branch::where('id', $allocation->seat->branch_id))->exists(),
            404
        );

        $allocation->update(['status' => 'cancelled', 'end_date' => now()]);

        return response()->json(['message' => 'Seat released.']);
    }
}
