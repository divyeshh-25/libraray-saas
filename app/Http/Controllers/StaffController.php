<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    use ScopesToViewer;

    public function index(): View
    {
        $staff = $this->scopeThroughUser(Staff::query())->with(['user.branch', 'roles'])->latest()->get();
        $branches = $this->scopeBranchRow(Branch::query())->orderBy('name')->pluck('name');

        return view('staff.index', compact('staff', 'branches'));
    }

    public function create(): View
    {
        return view('staff.form', ['mode' => 'create'] + $this->options());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, true);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'tenant_id' => Auth::user()->tenant_id,
                'branch_id' => $this->resolveBranchId($data['branch']),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'user_type' => 'staff',
                'status' => strtolower($data['status']),
            ]);

            $staff = Staff::create([
                'user_id' => $user->id,
                'employee_code' => $data['code'] ?? null,
                'designation' => $data['designation'] ?? null,
                'joining_date' => $data['joined'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => strtolower($data['status']),
            ]);

            if ($role = Role::where('name', $data['role'])->first()) {
                $staff->roles()->sync([$role->id => ['created_at' => now()]]);
            }
        });

        return redirect()->route('staff.index')->with('success', 'Staff member added.');
    }

    public function edit(int $id): View
    {
        $staff = $this->scopeThroughUser(Staff::query())->with(['user', 'roles'])->findOrFail($id);

        return view('staff.form', ['mode' => 'edit', 'staff' => $staff] + $this->options());
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $staff = $this->scopeThroughUser(Staff::query())->with('user')->findOrFail($id);
        $data = $this->validateData($request, false);

        DB::transaction(function () use ($staff, $data) {
            $staff->user->update([
                'branch_id' => $this->resolveBranchId($data['branch']),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => strtolower($data['status']),
            ]);

            $staff->update([
                'employee_code' => $data['code'] ?? null,
                'designation' => $data['designation'] ?? null,
                'joining_date' => $data['joined'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => strtolower($data['status']),
            ]);

            if ($role = Role::where('name', $data['role'])->first()) {
                $staff->roles()->sync([$role->id => ['created_at' => now()]]);
            }
        });

        return redirect()->route('staff.index')->with('success', 'Staff member updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->scopeThroughUser(Staff::query())->findOrFail($id)->delete();

        return response()->json(['message' => 'Staff member archived.']);
    }

    public function archived(): View
    {
        $staff = $this->scopeThroughUser(Staff::onlyTrashed())->with(['user.branch', 'roles'])->latest('deleted_at')->get();

        return view('staff.archived', compact('staff'));
    }

    public function restore(int $id): JsonResponse
    {
        $this->scopeThroughUser(Staff::onlyTrashed())->findOrFail($id)->restore();

        return response()->json(['message' => 'Staff member restored.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'branches' => $this->scopeBranchRow(Branch::query())->orderBy('name')->get()->map(fn (Branch $b) => "{$b->name} ({$b->code})"),
            'roles' => Role::orderBy('name')->pluck('name')->unique()->values(),
        ];
    }

    private function resolveBranchId(string $label): ?int
    {
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
            'code' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'branch' => ['required', 'string'],
            'designation' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'string'],
            'joined' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ];

        if ($isCreate) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $request->validate($rules);
    }
}
