<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait FilterableByTenant
{
    /**
     * Scope a query to filter by tenant_id.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTenant(Builder $query, mixed $tenantId = null): Builder
    {
        // 1. If a specific ID is passed, filter by it immediately.
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }

        // 2. If no ID is passed, check if the API request contains one (e.g., ?tenant_id=5)
        if (request()->filled('tenant_id')) {
            return $query->where('tenant_id', request('tenant_id'));
        }

        // 3. If neither is provided, return the query unmodified so the admin sees all records.
        return $query;
    }
}
