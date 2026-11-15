<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['branch_id', 'hall_id', 'name', 'floor_number', 'width', 'height', 'status'])]
class Floor extends Model
{
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(FloorPlanObject::class);
    }
}
