<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FeeStructure;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeStructureController extends Controller
{
    public function index(): View
    {
        $fees = FeeStructure::query()->with(['branch', 'shift'])->latest()->get();
        $branches = Branch::orderBy('name')->pluck('name');

        return view('settings.fee-structure', compact('fees', 'branches'));
    }

    public function edit(int $id): View
    {
        $fee = FeeStructure::with(['branch', 'shift'])->findOrFail($id);
        $branches = Branch::orderBy('name')->pluck('name');

        return view('settings.fee-structure-edit', compact('fee', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        FeeStructure::create([
            'branch_id' => $this->resolveBranchId($data['branch']),
            'shift_id' => $this->resolveShiftId($data['branch'], $data['shift']),
            'name' => $data['name'],
            'amount' => $data['amount'],
            'duration_type' => strtolower($data['duration']),
            'status' => 'active',
        ]);

        return redirect()->route('settings.fees')->with('success', 'Fee structure added.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $fee = FeeStructure::findOrFail($id);
        $data = $this->validateData($request);

        $fee->update([
            'branch_id' => $this->resolveBranchId($data['branch']),
            'shift_id' => $this->resolveShiftId($data['branch'], $data['shift']),
            'name' => $data['name'],
            'amount' => $data['amount'],
            'duration_type' => strtolower($data['duration']),
        ]);

        return redirect()->route('settings.fees')->with('success', 'Fee structure updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        FeeStructure::findOrFail($id)->delete();

        return response()->json(['message' => 'Fee structure archived.']);
    }

    public function archived(): View
    {
        $fees = FeeStructure::onlyTrashed()->with(['branch', 'shift'])->latest('deleted_at')->get();

        return view('settings.fee-structure-archived', compact('fees'));
    }

    public function restore(int $id): JsonResponse
    {
        FeeStructure::onlyTrashed()->findOrFail($id)->restore();

        return response()->json(['message' => 'Fee structure restored.']);
    }

    private function resolveBranchId(string $name): int
    {
        return Branch::where('name', $name)->firstOrFail()->id;
    }

    private function resolveShiftId(string $branchName, string $shiftName): int
    {
        $branchId = $this->resolveBranchId($branchName);

        return Shift::where('branch_id', $branchId)->where('name', $shiftName)->firstOrFail()->id;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'branch' => ['required', 'string'],
            'shift' => ['required', 'string'],
            'duration' => ['required', 'string', 'in:Daily,Weekly,Monthly,Quarterly,Yearly'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
