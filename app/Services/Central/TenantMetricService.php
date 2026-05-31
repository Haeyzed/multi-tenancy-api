<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\TenantMetric;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Central TenantMetric records and queries.
 */
class TenantMetricService
{
    /**
     * Get all TenantMetric records.
     *
     * @return Collection<int, TenantMetric>
     */
    public function getAll(): Collection
    {
        return TenantMetric::query()->get();
    }

    /**
     * Get paginated TenantMetric records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, TenantMetric>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return TenantMetric::query()->paginate($perPage);
    }

    /**
     * Find TenantMetric by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?TenantMetric
    {
        return TenantMetric::query()->find($id);
    }

    /**
     * Find TenantMetric by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): TenantMetric
    {
        return TenantMetric::query()->findOrFail($id);
    }

    /**
     * Create a new TenantMetric.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TenantMetric
    {
        return TenantMetric::query()->create($data);
    }

    /**
     * Update TenantMetric.
     *
     * @param  TenantMetric  $tenantMetric  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(TenantMetric $tenantMetric, array $data): TenantMetric
    {
        $tenantMetric->query()->update($data);

        return $tenantMetric->fresh();
    }

    /**
     * Delete TenantMetric.
     *
     * @param  TenantMetric  $tenantMetric  The model instance to delete.
     */
    public function delete(TenantMetric $tenantMetric): bool
    {
        return $tenantMetric->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, TenantMetric>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return TenantMetric::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter records.
     *
     * @param  string  $start  Start date (inclusive).
     * @param  string  $end  End date (inclusive).
     * @return Collection<int, TenantMetric>
     */
    public function getByDateRange(string $start, string $end): Collection
    {
        return TenantMetric::query()->whereBetween('metric_date', [Carbon::parse($start), Carbon::parse($end)])->get();
    }
}
