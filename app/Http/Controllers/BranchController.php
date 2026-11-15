<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BranchController extends Controller
{
    use ScopesToViewer;

    public function index(): View
    {
        $branches = $this->scopeBranchRow(Branch::query())
            ->with('tenant')
            ->withCount(['users as students_count' => fn ($q) => $q->where('user_type', 'student')])
            ->latest()
            ->get()
            ->map(function (Branch $branch) {
                $branch->halls_count = DB::table('halls')->where('branch_id', $branch->id)->count();
                $branch->seats_count = DB::table('seats')->where('branch_id', $branch->id)->count();

                return $branch;
            });

        $tenant = Tenant::find($this->viewerTenantId());
        $tenants = collect([$tenant->library_name]);
        $cities = Tenant::where('id', $this->viewerTenantId())->whereNotNull('city')->pluck('city');

        return view('branches.index', compact('branches', 'tenants', 'cities'));
    }

    public function create(Request $request): View
    {
        $tenants = collect([Tenant::find($this->viewerTenantId())->library_name]);
        $data = ['mode' => 'create', 'tenants' => $tenants];

        return view($request->ajax() ? 'branches._form' : 'branches.form', $data);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validateData($request);

        $branch = Branch::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $data['name'],
            'code' => $data['code'],
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => strtolower($data['status']),
            'opens_at' => $data['opens'] ?? null,
            'closes_at' => $data['closes'] ?? null,
            'weekly_off' => $data['off'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Branch created.', 'branch' => $branch]);
        }

        return redirect()->route('branches.index')->with('success', 'Branch created.');
    }

    public function show(int $id): View
    {
        $branch = $this->scopeBranchRow(Branch::withTrashed())
            ->with('tenant')
            ->withCount(['users as students_count' => fn ($q) => $q->where('user_type', 'student')])
            ->findOrFail($id);

        $halls = DB::table('halls')->where('branch_id', $branch->id)->get();
        $seatsCount = DB::table('seats')->where('branch_id', $branch->id)->count();
        $occupancy = $seatsCount > 0 ? round($branch->students_count / $seatsCount * 100) : 0;

        return view('branches.show', compact('branch', 'halls', 'seatsCount', 'occupancy'));
    }

    public function edit(Request $request, int $id): View
    {
        $branch = $this->scopeBranchRow(Branch::query())->findOrFail($id);
        $tenants = collect([Tenant::find($this->viewerTenantId())->library_name]);
        $data = ['mode' => 'edit', 'branch' => $branch, 'tenants' => $tenants];

        return view($request->ajax() ? 'branches._form' : 'branches.form', $data);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $branch = $this->scopeBranchRow(Branch::query())->findOrFail($id);
        $data = $this->validateData($request);

        $branch->update([
            'name' => $data['name'],
            'code' => $data['code'],
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => strtolower($data['status']),
            'opens_at' => $data['opens'] ?? null,
            'closes_at' => $data['closes'] ?? null,
            'weekly_off' => $data['off'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Branch updated.', 'branch' => $branch]);
        }

        return redirect()->route('branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $branch = $this->scopeBranchRow(Branch::query())->findOrFail($id);
        $branch->delete();

        return response()->json(['message' => 'Branch archived.']);
    }

    public function archived(): View
    {
        $branches = $this->scopeBranchRow(Branch::onlyTrashed())
            ->with('tenant')
            ->latest('deleted_at')
            ->get();

        return view('branches.archived', compact('branches'));
    }

    public function restore(int $id): JsonResponse
    {
        $branch = $this->scopeBranchRow(Branch::onlyTrashed())->findOrFail($id);
        $branch->restore();

        return response()->json(['message' => 'Branch restored.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'tenant' => ['required', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'opens' => ['nullable'],
            'closes' => ['nullable'],
            'off' => ['nullable', 'string'],
        ]);
    }
}
