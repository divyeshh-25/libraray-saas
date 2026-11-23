<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\FeeInvoice;
use App\Models\FeeStructure;
use App\Models\FeeTransaction;
use App\Models\Student;
use App\Models\User;
use App\Services\FeeBilling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
        $invoices = $student->invoices()->with('transactions')->latest('invoice_date')->latest('id')->take(10)->get();

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

    /**
     * Take a payment from a student. It is applied to their oldest invoice
     * with a balance (or a chosen invoice). With no outstanding invoice, a
     * one-off invoice is raised for the amount and settled in full.
     */
    public function collectFee(Request $request, int $id, FeeBilling $billing): JsonResponse
    {
        $student = $this->scopeThroughUser(Student::query())->findOrFail($id);

        $data = $request->validate([
            'fee_structure_id' => ['nullable', 'integer'],
            'invoice_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'paid_on' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', Rule::in(FeeTransaction::METHODS)],
            'reference_number' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $paidOn = Carbon::parse($data['paid_on']);

        $transaction = DB::transaction(function () use ($data, $student, $billing, $paidOn) {
            $invoiceQuery = FeeInvoice::outstanding()->where('student_id', $student->id);

            if (! empty($data['invoice_id'])) {
                $invoice = $invoiceQuery->whereKey($data['invoice_id'])->first();
                if (! $invoice) {
                    throw ValidationException::withMessages(['invoice_id' => 'That invoice is not payable for this student.']);
                }
            } else {
                $invoice = $invoiceQuery->orderBy('invoice_date')->orderBy('id')->first();
            }

            if (! $invoice) {
                $feeStructureId = $data['fee_structure_id'] ?? null;
                if ($feeStructureId && ! FeeStructure::whereKey($feeStructureId)
                    ->whereHas('branch', fn ($q) => $q->where('tenant_id', $this->viewerTenantId()))->exists()) {
                    throw ValidationException::withMessages(['fee_structure_id' => 'Invalid fee plan.']);
                }

                $invoice = $billing->createInvoice(
                    student: $student,
                    amount: number_format((float) $data['amount'], 2, '.', ''),
                    invoiceDate: $paidOn,
                    dueDate: $paidOn,
                    feeStructureId: $feeStructureId,
                    notes: $data['notes'] ?? null,
                );
            }

            return $billing->recordPayment(
                invoice: $invoice,
                amount: $data['amount'],
                method: $data['payment_method'] ?? 'cash',
                paidAt: $paidOn->copy()->setTimeFrom(now()),
                reference: $data['reference_number'] ?? null,
                remarks: $data['notes'] ?? null,
                receivedBy: Auth::id(),
            );
        });

        return response()->json([
            'message' => "Payment received. Receipt {$transaction->receipt_number} issued.",
            'receipt_url' => route('receipts.show', $transaction->id),
            'invoice' => $transaction->invoice,
        ]);
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
     * @return Collection<int, string>
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
