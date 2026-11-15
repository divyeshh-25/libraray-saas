<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Branch;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Shift;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HallController extends Controller
{
    use ScopesToViewer;

    public function index(): View
    {
        $halls = $this->scopeThroughBranch(Hall::query())
            ->with('branch.tenant')
            ->latest()
            ->get();

        $branches = $this->visibleBranches()->map(fn (Branch $b) => $b->name);

        return view('halls.index', compact('halls', 'branches'));
    }

    public function create(Request $request): View
    {
        $branches = $this->branchOptions();
        $data = ['mode' => 'create', 'branches' => $branches];

        return view($request->ajax() ? 'halls._form' : 'halls.form', $data);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validateData($request);

        $hall = Hall::create([
            'branch_id' => $this->resolveBranchId($data['branch']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => strtolower($data['status']),
            'floors' => $data['floors'] ?? 1,
            'total_seats' => $data['total_seats'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Hall created.', 'hall' => $hall]);
        }

        return redirect()->route('halls.index')->with('success', 'Hall created.');
    }

    public function show(int $id): View
    {
        $hall = $this->scopeThroughBranch(Hall::withTrashed())->with('branch.tenant')->findOrFail($id);

        $seatRows = Seat::where('branch_id', $hall->branch_id)
            ->where('status', 'active')
            ->whereHas('floorPlanObject.floor', fn ($q) => $q->where('hall_id', $hall->id))
            ->with(['floorPlanObject', 'allocations' => fn ($q) => $q->where('status', 'active')->with(['student.user', 'shift'])])
            ->get()
            // seat_number is free text (e.g. "S1".."S10"); a plain DB sort
            // orders it lexically, so sort naturally once loaded instead.
            ->sortBy(fn (Seat $seat) => $seat->seat_number, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $seats = $seatRows->isNotEmpty() ? $seatRows->count() : (int) ($hall->total_seats ?? 0);
        $occupied = $seatRows->filter(fn (Seat $seat) => $seat->activeAllocation() !== null)->count();

        $students = Student::whereHas('user', fn ($q) => $q->where('branch_id', $hall->branch_id))
            ->with('user')
            ->get();

        if (! Shift::where('branch_id', $hall->branch_id)->exists()) {
            Shift::seedDefaultsForBranch($hall->branch_id);
        }

        $shifts = Shift::where('branch_id', $hall->branch_id)->where('status', 'active')->get();

        return view('halls.show', compact('hall', 'seats', 'occupied', 'seatRows', 'students', 'shifts'));
    }

    public function edit(Request $request, int $id): View
    {
        $hall = $this->scopeThroughBranch(Hall::query())->findOrFail($id);
        $branches = $this->branchOptions();
        $data = ['mode' => 'edit', 'hall' => $hall, 'branches' => $branches];

        return view($request->ajax() ? 'halls._form' : 'halls.form', $data);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $hall = $this->scopeThroughBranch(Hall::query())->findOrFail($id);
        $data = $this->validateData($request);

        $hall->update([
            'branch_id' => $this->resolveBranchId($data['branch']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => strtolower($data['status']),
            'floors' => $data['floors'] ?? 1,
            'total_seats' => $data['total_seats'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Hall updated.', 'hall' => $hall]);
        }

        return redirect()->route('halls.index')->with('success', 'Hall updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $hall = $this->scopeThroughBranch(Hall::query())->findOrFail($id);
        $hall->delete();

        return response()->json(['message' => 'Hall archived.']);
    }

    public function archived(): View
    {
        $halls = $this->scopeThroughBranch(Hall::onlyTrashed())
            ->with('branch.tenant')
            ->latest('deleted_at')
            ->get();

        return view('halls.archived', compact('halls'));
    }

    public function restore(int $id): JsonResponse
    {
        $hall = $this->scopeThroughBranch(Hall::onlyTrashed())->findOrFail($id);
        $hall->restore();

        return response()->json(['message' => 'Hall restored.']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Branch>
     */
    private function visibleBranches()
    {
        return $this->scopeBranchRow(Branch::query())->orderBy('name')->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function branchOptions()
    {
        return $this->visibleBranches()->map(fn (Branch $b) => "{$b->name} ({$b->code})");
    }

    private function resolveBranchId(string $label): int
    {
        $code = preg_match('/\(([^)]+)\)$/', $label, $m) ? $m[1] : $label;

        return $this->scopeBranchRow(Branch::where('code', $code))->firstOrFail()->id;
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
            'total_seats' => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
