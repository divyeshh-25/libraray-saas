<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\FeeInvoice;
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
        $students = $this->scopeThroughUser(Student::query())->with(['user.branch', 'enrollments' => fn ($q) => $q->where('status', 'active')->with(['shift', 'feeStructure', 'seatAllocation.seat'])])->latest()->get();
        $branches = $this->scopeBranchRow(Branch::query())->orderBy('name')->pluck('name');

        return view('students.index', compact('students', 'branches'));
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
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Student admitted.');
    }

    public function show(int $id): View
    {
        $student = $this->scopeThroughUser(Student::withTrashed())->with(['user.branch', 'enrollments' => fn ($q) => $q->where('status', 'active')->with(['shift', 'feeStructure', 'seatAllocation.seat'])])->findOrFail($id);
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
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Student updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->scopeThroughUser(Student::query())->findOrFail($id)->delete();

        return response()->json(['message' => 'Student archived.']);
    }

    public function collectFee(Request $request, int $id): JsonResponse
    {
        $student = $this->scopeThroughUser(Student::query())->findOrFail($id);

        $data = $request->validate([
            'fee_structure_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $invoice = FeeInvoice::create([
            'student_id' => $student->id,
            'fee_structure_id' => $data['fee_structure_id'] ?? null,
            'invoice_number' => 'INV-'.now()->format('ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'invoice_date' => $data['paid_on'],
            'due_date' => $data['paid_on'],
            'total_amount' => $data['amount'],
            'discount_amount' => 0,
            'final_amount' => $data['amount'],
            'status' => 'paid',
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['message' => 'Fee collected.', 'invoice' => $invoice]);
    }

    public function enrollmentHistory(int $id): View
    {
        $student = $this->scopeThroughUser(Student::query())->with('user')->findOrFail($id);
        $enrollments = $student->enrollments()->with(['branch', 'shift', 'feeStructure', 'seatAllocation.seat'])->withTrashed()->latest('start_date')->get();

        return view('students._history', compact('student', 'enrollments'));
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
            'admitted' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ];

        if ($isCreate) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $request->validate($rules);
    }
}
