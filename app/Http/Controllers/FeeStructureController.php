<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\FeeStructure;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeStructureController extends Controller
{
    use ScopesToViewer;

    public function index(): View
    {
        $fees = $this->scopeThroughBranch(FeeStructure::query())->with(['branch', 'shift'])->latest()->get();
        $branches = $this->scopeBranchRow(Branch::query())->orderBy('name')->pluck('name');

        return view('settings.fee-structure', compact('fees', 'branches'));
    }

    public function edit(int $id): View
    {
        $fee = $this->scopeThroughBranch(FeeStructure::query())->with(['branch', 'shift'])->findOrFail($id);
        $branches = $this->scopeBranchRow(Branch::query())->orderBy('name')->pluck('name');

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
        $fee = $this->scopeThroughBranch(FeeStructure::query())->findOrFail($id);
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
        $this->scopeThroughBranch(FeeStructure::query())->findOrFail($id)->delete();

        return response()->json(['message' => 'Fee structure archived.']);
    }

    public function archived(): View
    {
        $fees = $this->scopeThroughBranch(FeeStructure::onlyTrashed())->with(['branch', 'shift'])->latest('deleted_at')->get();

        return view('settings.fee-structure-archived', compact('fees'));
    }

    public function restore(int $id): JsonResponse
    {
        $this->scopeThroughBranch(FeeStructure::onlyTrashed())->findOrFail($id)->restore();

        return response()->json(['message' => 'Fee structure restored.']);
    }

    private function resolveBranchId(string $name): int
    {
        return $this->scopeBranchRow(Branch::where('name', $name))->firstOrFail()->id;
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
