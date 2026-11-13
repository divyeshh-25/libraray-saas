<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'price', 'billing_cycle', 'max_branches', 'max_students', 'features', 'status'])]
class Plan extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'features' => 'array',
        ];
    }

    public function tenantPlans(): HasMany
    {
        return $this->hasMany(TenantPlan::class);
    }
}
