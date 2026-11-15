<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['floor_plan_object_id', 'branch_id', 'seat_number', 'seat_type', 'status'])]
class Seat extends Model
{
    public function floorPlanObject(): BelongsTo
    {
        return $this->belongsTo(FloorPlanObject::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(StudentSeatAllocation::class);
    }

    public function activeAllocation(): ?StudentSeatAllocation
    {
        return $this->allocations->firstWhere('status', 'active');
    }
}
