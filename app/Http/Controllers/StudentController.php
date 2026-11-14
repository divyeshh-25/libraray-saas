<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    use ScopesToViewer;

    public function index(): View
    {
        $students = $this->scopeThroughUser(Student::query())->with('user.branch')->latest()->get();

        return view('students.index', compact('students'));
    }

    public function create(): View
    {
        return view('students.form', ['mode' => 'create', 'branches' => $this->branchOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, true);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'tenant_id' => Auth::user()->tenant_id,
                'branch_id' => $this->resolveBranchId($data['branch'] ?? null),
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password'] ?? str()->random(12)),
                'user_type' => 'student',
                'status' => strtolower($data['status']),
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_code' => 'STU-'.now()->format('ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                'admission_date' => $data['admitted'] ?? null,
                'date_of_birth' => $data['dob'] ?? null,
                'address' => $data['address'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
                'status' => strtolower($data['status']),
                'shift' => $data['shift'] ?? null,
                'seat_label' => $data['seat'] ?? null,
                'fee_amount' => $this->parseFee($data['fee'] ?? null),
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Student admitted.');
    }

    public function show(int $id): View
    {
        $student = $this->scopeThroughUser(Student::withTrashed())->with('user.branch')->findOrFail($id);
        $attendances = $student->attendances()->latest('attendance_date')->take(28)->get();
        $invoices = $student->invoices()->latest('invoice_date')->take(6)->get();

        return view('students.show', compact('student', 'attendances', 'invoices'));
    }

    public function edit(int $id): View
    {
        $student = $this->scopeThroughUser(Student::query())->with('user.branch')->findOrFail($id);

        return view('students.form', ['mode' => 'edit', 'student' => $student, 'branches' => $this->branchOptions()]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $student = $this->scopeThroughUser(Student::query())->with('user')->findOrFail($id);
        $data = $this->validateData($request, false);

        DB::transaction(function () use ($student, $data) {
            $student->user->update([
                'branch_id' => $this->resolveBranchId($data['branch'] ?? null),
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => strtolower($data['status']),
            ]);

            $student->update([
                'admission_date' => $data['admitted'] ?? null,
                'date_of_birth' => $data['dob'] ?? null,
                'address' => $data['address'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
                'status' => strtolower($data['status']),
                'shift' => $data['shift'] ?? null,
                'seat_label' => $data['seat'] ?? null,
                'fee_amount' => $this->parseFee($data['fee'] ?? null),
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Student updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->scopeThroughUser(Student::query())->findOrFail($id)->delete();

        return response()->json(['message' => 'Student archived.']);
    }

    public function archived(): View
    {
        $students = $this->scopeThroughUser(Student::onlyTrashed())->with('user.branch')->latest('deleted_at')->get();

        return view('students.archived', compact('students'));
    }

    public function restore(int $id): JsonResponse
    {
        $this->scopeThroughUser(Student::onlyTrashed())->findOrFail($id)->restore();

        return response()->json(['message' => 'Student restored.']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function branchOptions()
    {
        return $this->scopeBranchRow(Branch::query())->orderBy('name')->get()->map(fn (Branch $b) => "{$b->name} ({$b->code})");
    }

    private function resolveBranchId(?string $label): ?int
    {
        if (! $label) {
            return null;
        }

        $code = preg_match('/\(([^)]+)\)$/', $label, $m) ? $m[1] : null;

        return $code ? $this->scopeBranchRow(Branch::where('code', $code))->first()?->id : null;
    }

    private function parseFee(?string $label): ?float
    {
        if (! $label) {
            return null;
        }

        preg_match('/[\d,]+(\.\d+)?/', $label, $m);

        return isset($m[0]) ? (float) str_replace(',', '', $m[0]) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, bool $isCreate): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'dob' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'branch' => ['required', 'string'],
            'shift' => ['nullable', 'string'],
            'fee' => ['nullable', 'string'],
            'seat' => ['nullable', 'string', 'max:50'],
            'admitted' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ];

        if ($isCreate) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $request->validate($rules);
    }
}
