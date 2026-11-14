<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Every tenant module (branches, halls, staff, students, fee structures) must
 * never leak across tenants. On top of that, a viewer who is tied to one
 * branch (branch_id is set) only sees that branch's data; the owner or any
 * staff not pinned to a branch sees the whole tenant.
 */
trait ScopesToViewer
{
    private function viewerTenantId(): int
    {
        return Auth::user()->tenant_id;
    }

    private function viewerBranchId(): ?int
    {
        return Auth::user()->branch_id;
    }

    /**
     * For models with their own tenant_id column (Tenant's branches table
     * itself: the branch row is the thing being scoped, by its own id).
     */
    private function scopeBranchRow(Builder $query): Builder
    {
        $query->where('tenant_id', $this->viewerTenantId());

        if ($branchId = $this->viewerBranchId()) {
            $query->where('id', $branchId);
        }

        return $query;
    }

    /**
     * For models that belong directly to a branch (Hall, FeeStructure, Shift):
     * scope through the branch relation's tenant_id, and to the viewer's own
     * branch if they're pinned to one.
     */
    private function scopeThroughBranch(Builder $query, string $relation = 'branch'): Builder
    {
        $tenantId = $this->viewerTenantId();
        $branchId = $this->viewerBranchId();

        return $query->whereHas($relation, function (Builder $q) use ($tenantId, $branchId) {
            $q->where('tenant_id', $tenantId);

            if ($branchId) {
                $q->where('id', $branchId);
            }
        });
    }

    /**
     * For models that belong to a User (Staff, Student): scope through the
     * user's tenant_id/branch_id.
     */
    private function scopeThroughUser(Builder $query, string $relation = 'user'): Builder
    {
        $tenantId = $this->viewerTenantId();
        $branchId = $this->viewerBranchId();

        return $query->whereHas($relation, function (Builder $q) use ($tenantId, $branchId) {
            $q->where('tenant_id', $tenantId);

            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        });
    }
}
