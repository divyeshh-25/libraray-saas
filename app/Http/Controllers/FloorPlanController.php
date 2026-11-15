<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\Floor;
use App\Models\FloorPlanObject;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Shift;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FloorPlanController extends Controller
{
    use ScopesToViewer;

    private const OBJECT_TYPES = ['WALL', 'BLOCKER', 'SEAT', 'WASHROOM', 'ENTRY_GATE', 'RECEPTION', 'OFFICE', 'OTHER'];

    public function designer(Request $request, int $hallId): View
    {
        $hall = $this->scopeThroughBranch(Hall::query())->findOrFail($hallId);
        $floor = $this->resolveFloor($hall, $request->integer('floor', 1));

        $floor->load([
            'objects' => fn ($q) => $q->where('status', 'active')->orderBy('z_index'),
            'objects.seat.allocations' => fn ($q) => $q->where('status', 'active')->with(['student.user', 'shift']),
        ]);

        $plan = [
            'format' => 'library-floor-plan',
            'version' => 1,
            'floor' => [
                'name' => $floor->name,
                'width' => (float) $floor->width,
                'height' => (float) $floor->height,
            ],
            'objects' => $floor->objects->map(fn (FloorPlanObject $o) => [
                'id' => $o->id,
                'type' => $o->type,
                'name' => $o->title,
                'x' => (float) $o->x,
                'y' => (float) $o->y,
                'w' => (float) $o->width,
                'h' => (float) $o->height,
                'rotation' => (float) $o->rotation,
                'detail' => $o->properties['detail'] ?? '',
            ])->values(),
        ];

        // Keyed by floor_plan_object id so the designer's property panel can
        // show allocation state for a SEAT once it's selected on the canvas.
        $seatMeta = $floor->objects
            ->filter(fn (FloorPlanObject $o) => $o->type === 'SEAT' && $o->seat)
            ->mapWithKeys(function (FloorPlanObject $o) {
                $allocation = $o->seat->activeAllocation();

                return [$o->id => [
                    'seatId' => $o->seat->id,
                    'allocationId' => $allocation?->id,
                    'studentName' => $allocation?->student?->user?->name,
                    'shiftName' => $allocation?->shift?->name,
                ]];
            });

        if (! Shift::where('branch_id', $hall->branch_id)->exists()) {
            Shift::seedDefaultsForBranch($hall->branch_id);
        }

        $students = Student::whereHas('user', fn ($q) => $q->where('branch_id', $hall->branch_id))
            ->with('user')
            ->get();

        $shifts = Shift::where('branch_id', $hall->branch_id)->where('status', 'active')->get();

        $principal = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
        $canAllocate = $principal?->canDo('halls', 'update') ?? false;

        return view('floor-plans.designer', [
            'hall' => $hall,
            'floor' => $floor,
            'plan' => $plan,
            'seatMeta' => $seatMeta,
            'students' => $students,
            'shifts' => $shifts,
            'canAllocate' => $canAllocate,
            'hasExistingPlan' => $floor->objects->isNotEmpty(),
        ]);
    }

    public function save(Request $request, int $hallId): JsonResponse
    {
        $hall = $this->scopeThroughBranch(Hall::query())->findOrFail($hallId);
        $floor = $this->resolveFloor($hall, $request->integer('floor', 1));

        $data = $request->validate([
            'floor' => ['required', 'array'],
            'floor.name' => ['required', 'string', 'max:150'],
            'floor.width' => ['required', 'numeric', 'min:300', 'max:3000'],
            'floor.height' => ['required', 'numeric', 'min:250', 'max:2000'],
            'objects' => ['array'],
            'objects.*.id' => ['nullable', 'integer'],
            'objects.*.type' => ['required', 'string', 'in:'.implode(',', self::OBJECT_TYPES)],
            'objects.*.name' => ['nullable', 'string', 'max:150'],
            'objects.*.x' => ['required', 'numeric'],
            'objects.*.y' => ['required', 'numeric'],
            'objects.*.w' => ['required', 'numeric', 'min:1'],
            'objects.*.h' => ['required', 'numeric', 'min:1'],
            'objects.*.rotation' => ['nullable', 'numeric'],
            'objects.*.detail' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($hall, $floor, $data) {
            $floor->update([
                'name' => $data['floor']['name'],
                'width' => $data['floor']['width'],
                'height' => $data['floor']['height'],
            ]);

            $existingIds = $floor->objects()->where('status', 'active')->pluck('id')->all();
            $keptIds = [];

            foreach (($data['objects'] ?? []) as $index => $o) {
                $attributes = [
                    'floor_id' => $floor->id,
                    'type' => $o['type'],
                    'title' => $o['name'] ?? $o['type'],
                    'x' => $o['x'],
                    'y' => $o['y'],
                    'width' => $o['w'],
                    'height' => $o['h'],
                    'rotation' => $o['rotation'] ?? 0,
                    'properties' => ['detail' => $o['detail'] ?? ''],
                    'z_index' => $index,
                    'status' => 'active',
                ];

                // The client sends its own object id, which is only a real
                // database id when it was loaded from this floor; otherwise
                // it's a locally generated counter and must not be reused.
                $isExisting = isset($o['id']) && in_array($o['id'], $existingIds, true);

                $object = $isExisting
                    ? tap(FloorPlanObject::find($o['id']))->update($attributes)
                    : FloorPlanObject::create($attributes);

                $keptIds[] = $object->id;

                if ($o['type'] === 'SEAT') {
                    $this->syncSeat($hall, $object);
                } else {
                    $object->seat()?->delete();
                }
            }

            $removedIds = array_diff($existingIds, $keptIds);

            if ($removedIds) {
                // Retired rather than deleted: a seat with allocation history
                // is foreign-key-protected, so its backing object must stay.
                $this->retireSeatsFor($removedIds);
                FloorPlanObject::whereIn('id', $removedIds)->update(['status' => 'retired']);
            }
        });

        return response()->json(['message' => 'Floor plan saved.']);
    }

    private function resolveFloor(Hall $hall, int $floorNumber): Floor
    {
        return Floor::firstOrCreate(
            ['hall_id' => $hall->id, 'floor_number' => $floorNumber],
            [
                'branch_id' => $hall->branch_id,
                'name' => $floorNumber === 1 ? 'Ground Floor' : "Floor {$floorNumber}",
                'width' => 1000,
                'height' => 650,
                'status' => 'active',
            ]
        );
    }

    private function syncSeat(Hall $hall, FloorPlanObject $object): void
    {
        Seat::updateOrCreate(
            ['floor_plan_object_id' => $object->id],
            [
                'branch_id' => $hall->branch_id,
                'seat_number' => $object->title ?: "Seat-{$object->id}",
                'status' => 'active',
            ]
        );
    }

    /**
     * Seats removed from the designer are retired rather than deleted; any of
     * their active student allocations are cancelled rather than silently
     * orphaned, preserving student/allocation history.
     */
    private function retireSeatsFor(array $floorPlanObjectIds): void
    {
        $seats = Seat::whereIn('floor_plan_object_id', $floorPlanObjectIds)->get();

        foreach ($seats as $seat) {
            $seat->allocations()->where('status', 'active')->update(['status' => 'cancelled', 'end_date' => now()]);
            $seat->update(['status' => 'retired']);
        }
    }
}
