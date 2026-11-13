<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->withCount('branches')
            ->withCount(['users as students_count' => fn ($q) => $q->where('user_type', 'student')])
            ->with('activePlan.plan')
            ->latest()
            ->get();

        return view('tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        $plans = Plan::where('status', 'active')->pluck('name');

        return view('tenants.form', ['mode' => 'create', 'plans' => $plans]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, true);

        DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'library_name' => $data['library_name'],
                'owner_name' => $data['owner_name'],
                'status' => $data['status'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
            ]);

            User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'user_type' => 'owner',
            ]);

            $this->syncPlan($tenant, $data);
            $this->logActivity($tenant, 'created', 'Tenant onboarded onto the platform.');
        });

        return redirect()->route('tenants.index')->with('success', 'Tenant created.');
    }

    public function show(int $id): View
    {
        $tenant = Tenant::withTrashed()
            ->withCount('branches')
            ->withCount(['users as students_count' => fn ($q) => $q->where('user_type', 'student')])
            ->with('activePlan.plan')
            ->findOrFail($id);

        $branches = $tenant->branches()->get()->map(function ($branch) {
            $branch->halls_count = DB::table('halls')->where('branch_id', $branch->id)->count();
            $branch->seats_count = DB::table('seats')->where('branch_id', $branch->id)->count();

            return $branch;
        });

        $billing = $tenant->tenantPlans()->with('plan')->latest('starts_at')->get();
        $activities = $tenant->activityLogs()->with('user')->latest()->take(5)->get();

        return view('tenants.show', compact('tenant', 'branches', 'billing', 'activities'));
    }

    public function edit(int $id): View
    {
        $tenant = Tenant::with('activePlan.plan')->findOrFail($id);
        $plans = Plan::where('status', 'active')->pluck('name');

        return view('tenants.form', ['mode' => 'edit', 'tenant' => $tenant, 'plans' => $plans]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $data = $this->validateData($request, false);

        DB::transaction(function () use ($tenant, $data) {
            $tenant->update([
                'library_name' => $data['library_name'],
                'owner_name' => $data['owner_name'],
                'status' => $data['status'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
            ]);

            $this->syncPlan($tenant, $data);
            $this->logActivity($tenant, 'updated', 'Tenant details were updated.');
        });

        return redirect()->route('tenants.index')->with('success', 'Tenant updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();
        $this->logActivity($tenant, 'archived', 'Tenant was moved to the archive.');

        return response()->json(['message' => 'Tenant archived.']);
    }

    public function archived(): View
    {
        $tenants = Tenant::onlyTrashed()
            ->withCount('branches')
            ->with('activePlan.plan')
            ->latest('deleted_at')
            ->get();

        return view('tenants.archived', compact('tenants'));
    }

    public function restore(int $id): JsonResponse
    {
        $tenant = Tenant::onlyTrashed()->findOrFail($id);
        $tenant->restore();
        $this->logActivity($tenant, 'restored', 'Tenant was restored from the archive.');

        return response()->json(['message' => 'Tenant restored.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, bool $isCreate): array
    {
        $rules = [
            'library_name' => ['required', 'string', 'max:200'],
            'owner_name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:Active,Trial,Suspended'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'plan' => ['required', 'string'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];

        if ($isCreate) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $data = $request->validate($rules);
        $data['status'] = strtolower($data['status']);

        return $data;
    }

    private function syncPlan(Tenant $tenant, array $data): void
    {
        $plan = Plan::where('name', $data['plan'])->first();

        if (! $plan) {
            return;
        }

        $tenant->tenantPlans()->where('status', 'active')->update(['status' => 'expired']);

        TenantPlan::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'starts_at' => $data['starts_at'],
            'expires_at' => $data['expires_at'] ?? null,
            'amount' => $plan->price,
            'status' => 'active',
        ]);
    }

    private function logActivity(Tenant $tenant, string $action, string $description): void
    {
        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth('web')->id(),
            'action' => $action,
            'module' => 'tenants',
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
