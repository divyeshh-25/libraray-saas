<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['owner_name', 'library_name', 'city', 'email', 'phone', 'address', 'status'])]
class Tenant extends Model
{
    use SoftDeletes;

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tenantPlans(): HasMany
    {
        return $this->hasMany(TenantPlan::class);
    }

    public function activePlan(): HasOne
    {
        return $this->hasOne(TenantPlan::class)->where('status', 'active')->latestOfMany('starts_at');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
