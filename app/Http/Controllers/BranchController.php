<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::query()
            ->with('tenant')
            ->withCount(['users as students_count' => fn ($q) => $q->where('user_type', 'student')])
            ->latest()
            ->get()
            ->map(function (Branch $branch) {
                $branch->halls_count = DB::table('halls')->where('branch_id', $branch->id)->count();
                $branch->seats_count = DB::table('seats')->where('branch_id', $branch->id)->count();

                return $branch;
            });

        $tenants = Tenant::orderBy('library_name')->pluck('library_name');
        $cities = Tenant::whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        return view('branches.index', compact('branches', 'tenants', 'cities'));
    }

    public function create(): View
    {
        $tenants = Tenant::orderBy('library_name')->pluck('library_name');

        return view('branches.form', ['mode' => 'create', 'tenants' => $tenants]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Branch::create([
            'tenant_id' => Tenant::where('library_name', $data['tenant'])->first()->id,
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

        return redirect()->route('branches.index')->with('success', 'Branch created.');
    }

    public function show(int $id): View
    {
        $branch = Branch::withTrashed()
            ->with('tenant')
            ->withCount(['users as students_count' => fn ($q) => $q->where('user_type', 'student')])
            ->findOrFail($id);

        $halls = DB::table('halls')->where('branch_id', $branch->id)->get();
        $seatsCount = DB::table('seats')->where('branch_id', $branch->id)->count();
        $occupancy = $seatsCount > 0 ? round($branch->students_count / $seatsCount * 100) : 0;

        return view('branches.show', compact('branch', 'halls', 'seatsCount', 'occupancy'));
    }

    public function edit(int $id): View
    {
        $branch = Branch::findOrFail($id);
        $tenants = Tenant::orderBy('library_name')->pluck('library_name');

        return view('branches.form', ['mode' => 'edit', 'branch' => $branch, 'tenants' => $tenants]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $branch = Branch::findOrFail($id);
        $data = $this->validateData($request);

        $branch->update([
            'tenant_id' => Tenant::where('library_name', $data['tenant'])->first()->id,
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

        return redirect()->route('branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json(['message' => 'Branch archived.']);
    }

    public function archived(): View
    {
        $branches = Branch::onlyTrashed()
            ->with('tenant')
            ->latest('deleted_at')
            ->get();

        return view('branches.archived', compact('branches'));
    }

    public function restore(int $id): JsonResponse
    {
        $branch = Branch::onlyTrashed()->findOrFail($id);
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
