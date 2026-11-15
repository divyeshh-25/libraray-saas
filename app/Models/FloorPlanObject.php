<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['floor_id', 'type', 'title', 'x', 'y', 'width', 'height', 'rotation', 'properties', 'z_index', 'status'])]
class FloorPlanObject extends Model
{
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function seat(): HasOne
    {
        return $this->hasOne(Seat::class);
    }
}
