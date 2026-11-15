<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function dashboard(): View
    {
        $student = $this->currentStudent();
        $enrollment = $student->activeEnrollment();
        $today = today();

        $todayAttendance = $enrollment
            ? StudentAttendance::where('student_id', $student->id)
                ->where('shift_id', $enrollment->shift_id)
                ->whereDate('attendance_date', $today)
                ->first()
            : null;

        $history = $student->attendances()->latest('attendance_date')->take(28)->get();

        $monthAttendances = $student->attendances()
            ->whereDate('attendance_date', '>=', $today->copy()->startOfMonth())
            ->whereDate('attendance_date', '<=', $today)
            ->get();
        $daysThisMonth = $today->day;
        $presentDaysThisMonth = $monthAttendances->whereIn('status', ['present', 'late'])->count();
        $consistency = $daysThisMonth > 0 ? round(($presentDaysThisMonth / $daysThisMonth) * 100) : 0;

        $avg = 0;
        if ($enrollment) {
            $daysSinceEnrollment = max(1, $enrollment->start_date->diffInDays($today) + 1);
            $presentAllTime = $student->attendances()->whereIn('status', ['present', 'late'])->count();
            $avg = round(($presentAllTime / $daysSinceEnrollment) * 100);
        }

        $invoices = $student->invoices()->latest('invoice_date')->take(10)->get();

        return view('student.dashboard', [
            'student' => $student,
            'enrollment' => $enrollment,
            'todayAttendance' => $todayAttendance,
            'history' => $history,
            'daysThisMonth' => $daysThisMonth,
            'presentDaysThisMonth' => $presentDaysThisMonth,
            'consistency' => $consistency,
            'avg' => $avg,
            'invoices' => $invoices,
        ]);
    }

    public function checkIn(): RedirectResponse
    {
        $student = $this->currentStudent();
        $enrollment = $student->activeEnrollment();

        abort_unless($enrollment, 422, 'You do not have an active enrollment. Contact your branch to check in.');

        $today = today();
        $attendance = StudentAttendance::where('student_id', $student->id)
            ->where('shift_id', $enrollment->shift_id)
            ->whereDate('attendance_date', $today)
            ->first();

        abort_if($attendance && $attendance->check_in, 422, 'You have already checked in today.');

        $now = now();
        $shiftStart = \Illuminate\Support\Carbon::parse($enrollment->shift->start_time);
        $status = $now->format('H:i') > $shiftStart->addMinutes(15)->format('H:i') ? 'late' : 'present';

        StudentAttendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'shift_id' => $enrollment->shift_id,
                'attendance_date' => $today,
            ],
            [
                'branch_id' => $enrollment->branch_id,
                'status' => $status,
                'check_in' => $now->format('H:i:s'),
                'marked_by' => Auth::id(),
            ]
        );

        return redirect()->route('student.dashboard')->with('success', 'Checked in successfully.');
    }

    public function checkOut(): RedirectResponse
    {
        $student = $this->currentStudent();
        $enrollment = $student->activeEnrollment();

        abort_unless($enrollment, 422, 'You do not have an active enrollment.');

        $today = today();
        $attendance = StudentAttendance::where('student_id', $student->id)
            ->where('shift_id', $enrollment->shift_id)
            ->whereDate('attendance_date', $today)
            ->first();

        abort_unless($attendance && $attendance->check_in, 422, 'You need to check in before you can check out.');
        abort_if($attendance->check_out, 422, 'You have already checked out today.');

        $attendance->update(['check_out' => now()->format('H:i:s')]);

        return redirect()->route('student.dashboard')->with('success', 'Checked out successfully.');
    }

    public function editProfile(): View
    {
        return view('student.profile', ['student' => $this->currentStudent()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $student = $this->currentStudent();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        $student->user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]);

        $student->update([
            'guardian_name' => $data['guardian_name'] ?? null,
            'guardian_phone' => $data['guardian_phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return redirect()->route('student.profile.edit')->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('student.profile.edit')->with('success', 'Password changed.');
    }

    private function currentStudent(): Student
    {
        return Auth::user()->student()->with(['enrollments.branch', 'enrollments.shift', 'enrollments.feeStructure', 'enrollments.seatAllocation.seat', 'user'])->firstOrFail();
    }
}
