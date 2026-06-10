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
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'tenant',
    ];

    /**
     * Get all TenantMetric records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, TenantMetric>
     */
    public function getAll(?string $search = null): Collection
    {
        return TenantMetric::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated TenantMetric records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, TenantMetric>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): LengthAwarePaginator {
        return TenantMetric::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterDateRange($startDate, $endDate)
            ->latest('metric_date')
            ->paginate($perPage);
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
        return TenantMetric::query()
            ->create($data)
            ->load(self::LIST_RELATIONS);
    }

    /**
     * Update TenantMetric.
     *
     * @param  TenantMetric  $tenantMetric  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(TenantMetric $tenantMetric, array $data): TenantMetric
    {
        $tenantMetric->update($data);

        return $tenantMetric->fresh(self::LIST_RELATIONS);
    }

    /**
     * Delete TenantMetric.
     *
     * @param  TenantMetric  $tenantMetric  The model instance to delete.
     */
    public function delete(TenantMetric $tenantMetric): bool
    {
        return $tenantMetric->delete();
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

    /**
     * KPI card metrics for tenant usage and revenue.
     *
     * @return list<array{key: string, label: string, value: int|float|string}>
     */
    public function getMetrics(): array
    {
        $query = TenantMetric::query()->forTenant();

        if (request()->filled('start_date') && request()->filled('end_date')) {
            $query->whereBetween('metric_date', [
                Carbon::parse(request('start_date')),
                Carbon::parse(request('end_date')),
            ]);
        }

        $aggregates = (clone $query)->selectRaw('
            COALESCE(SUM(total_orders), 0) as total_orders,
            COALESCE(SUM(total_revenue), 0) as total_revenue,
            COALESCE(SUM(total_products), 0) as total_products,
            COALESCE(SUM(total_customers), 0) as total_customers,
            COALESCE(SUM(storage_used_mb), 0) as storage_used_mb,
            COALESCE(SUM(bandwidth_used_mb), 0) as bandwidth_used_mb,
            COALESCE(SUM(api_calls), 0) as api_calls
        ')->first();

        $tenantsTracked = (clone $query)->distinct('tenant_id')->count('tenant_id');

        return [
            ['key' => 'tenants_tracked', 'label' => 'Tenants Tracked', 'value' => $tenantsTracked],
            ['key' => 'total_orders', 'label' => 'Total Orders', 'value' => (int) $aggregates->total_orders],
            ['key' => 'total_revenue', 'label' => 'Total Revenue', 'value' => (string) $aggregates->total_revenue],
            ['key' => 'total_products', 'label' => 'Total Products', 'value' => (int) $aggregates->total_products],
            ['key' => 'total_customers', 'label' => 'Total Customers', 'value' => (int) $aggregates->total_customers],
            ['key' => 'storage_used_mb', 'label' => 'Storage Used (MB)', 'value' => (int) $aggregates->storage_used_mb],
            ['key' => 'bandwidth_used_mb', 'label' => 'Bandwidth Used (MB)', 'value' => (int) $aggregates->bandwidth_used_mb],
            ['key' => 'api_calls', 'label' => 'API Calls', 'value' => (int) $aggregates->api_calls],
        ];
    }
}
