<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['branch_id', 'name', 'description', 'status', 'floors', 'total_seats'])]
class Hall extends Model
{
    use SoftDeletes;

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function floorPlans(): HasMany
    {
        return $this->hasMany(Floor::class);
    }

    /**
     * Seats actually placed in the floor plan designer, falling back to the
     * hall form's target seat count until a floor plan has been designed.
     */
    public function seatsCount(): int
    {
        $designed = Seat::where('status', 'active')
            ->whereHas('floorPlanObject.floor', fn ($q) => $q->where('hall_id', $this->id))
            ->count();

        return $designed > 0 ? $designed : (int) ($this->total_seats ?? 0);
    }
}
