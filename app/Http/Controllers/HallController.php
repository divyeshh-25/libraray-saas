<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Hall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HallController extends Controller
{
    public function index(): View
    {
        $halls = Hall::query()
            ->with('branch.tenant')
            ->latest()
            ->get();

        $branches = Branch::with('tenant')->orderBy('name')->get()
            ->map(fn (Branch $b) => $b->name);

        return view('halls.index', compact('halls', 'branches'));
    }

    public function create(): View
    {
        $branches = $this->branchOptions();

        return view('halls.form', ['mode' => 'create', 'branches' => $branches]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Hall::create([
            'branch_id' => $this->resolveBranchId($data['branch']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => strtolower($data['status']),
            'floors' => $data['floors'] ?? 1,
            'rows' => $data['rows'] ?? null,
            'cols' => $data['cols'] ?? null,
        ]);

        return redirect()->route('halls.index')->with('success', 'Hall created.');
    }

    public function show(int $id): View
    {
        $hall = Hall::withTrashed()->with('branch.tenant')->findOrFail($id);

        $seats = ($hall->rows ?? 0) * ($hall->cols ?? 0);

        return view('halls.show', compact('hall', 'seats'));
    }

    public function edit(int $id): View
    {
        $hall = Hall::findOrFail($id);
        $branches = $this->branchOptions();

        return view('halls.form', ['mode' => 'edit', 'hall' => $hall, 'branches' => $branches]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $hall = Hall::findOrFail($id);
        $data = $this->validateData($request);

        $hall->update([
            'branch_id' => $this->resolveBranchId($data['branch']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => strtolower($data['status']),
            'floors' => $data['floors'] ?? 1,
            'rows' => $data['rows'] ?? null,
            'cols' => $data['cols'] ?? null,
        ]);

        return redirect()->route('halls.index')->with('success', 'Hall updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $hall = Hall::findOrFail($id);
        $hall->delete();

        return response()->json(['message' => 'Hall archived.']);
    }

    public function archived(): View
    {
        $halls = Hall::onlyTrashed()
            ->with('branch.tenant')
            ->latest('deleted_at')
            ->get();

        return view('halls.archived', compact('halls'));
    }

    public function restore(int $id): JsonResponse
    {
        $hall = Hall::onlyTrashed()->findOrFail($id);
        $hall->restore();

        return response()->json(['message' => 'Hall restored.']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function branchOptions()
    {
        return Branch::orderBy('name')->get()->map(fn (Branch $b) => "{$b->name} ({$b->code})");
    }

    private function resolveBranchId(string $label): int
    {
        $code = preg_match('/\(([^)]+)\)$/', $label, $m) ? $m[1] : $label;

        return Branch::where('code', $code)->firstOrFail()->id;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'branch' => ['required', 'string'],
            'status' => ['required', 'string', 'in:Active,Maintenance,Inactive'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'floors' => ['nullable', 'integer', 'min:1'],
            'rows' => ['nullable', 'integer', 'min:1'],
            'cols' => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
