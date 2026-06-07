<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\HealthCheckStatus;
use App\Models\Central\TenantHealthCheck;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central TenantHealthCheck records and queries.
 */
class TenantHealthCheckService
{
    /**
     * Get all TenantHealthCheck records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, TenantHealthCheck>
     */
    public function getAll(?string $search = null): Collection
    {
        return TenantHealthCheck::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated TenantHealthCheck records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, TenantHealthCheck>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return TenantHealthCheck::query()
            ->forTenant()
            ->search($search)
            ->paginate($perPage);
    }

    /**
     * Find TenantHealthCheck by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?TenantHealthCheck
    {
        return TenantHealthCheck::query()->find($id);
    }

    /**
     * Find TenantHealthCheck by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): TenantHealthCheck
    {
        return TenantHealthCheck::query()->findOrFail($id);
    }

    /**
     * Create a new TenantHealthCheck.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TenantHealthCheck
    {
        return TenantHealthCheck::query()->create($data);
    }

    /**
     * Update TenantHealthCheck.
     *
     * @param  TenantHealthCheck  $tenantHealthCheck  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(TenantHealthCheck $tenantHealthCheck, array $data): TenantHealthCheck
    {
        $tenantHealthCheck->query()->update($data);

        return $tenantHealthCheck->fresh();
    }

    /**
     * Delete TenantHealthCheck.
     *
     * @param  TenantHealthCheck  $tenantHealthCheck  The model instance to delete.
     */
    public function delete(TenantHealthCheck $tenantHealthCheck): bool
    {
        return $tenantHealthCheck->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, TenantHealthCheck>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return TenantHealthCheck::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param  string  $status  Status value to filter by.
     * @return Collection<int, TenantHealthCheck>
     */
    public function getByStatus(string $status): Collection
    {
        return TenantHealthCheck::query()->where('status', $status)->get();
    }

    /**
     * Filter by check type.
     *
     * @param  string  $checkType  Health check type to filter by.
     * @return Collection<int, TenantHealthCheck>
     */
    public function getByCheckType(string $checkType): Collection
    {
        return TenantHealthCheck::query()->where('check_type', $checkType)->get();
    }

    /**
     * Get the latest health check per check type for a tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, TenantHealthCheck>
     */
    public function getLatestForTenant(string $tenantId): Collection
    {
        return TenantHealthCheck::query()->where('tenant_id', $tenantId)
            ->orderBy('checked_at', 'desc')
            ->get()
            ->unique('check_type')
            ->values();
    }

    /**
     * Get critical health checks.
     *
     * @return Collection<int, TenantHealthCheck>
     */
    public function getCritical(): Collection
    {
        return TenantHealthCheck::query()->where('status', HealthCheckStatus::Critical->value)
            ->with('tenant')
            ->orderBy('checked_at', 'desc')
            ->get();
    }

    /**
     * KPI card metrics for tenant health checks.
     *
     * @return list<array{key: string, label: string, value: int|float}>
     */
    public function getMetrics(): array
    {
        $query = TenantHealthCheck::query()->forTenant();

        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $avgResponseTime = (clone $query)
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');

        return [
            ['key' => 'total', 'label' => 'Total Checks', 'value' => (int) $counts->sum()],
            ['key' => 'healthy', 'label' => 'Healthy', 'value' => (int) ($counts[HealthCheckStatus::Healthy->value] ?? 0)],
            ['key' => 'warning', 'label' => 'Warning', 'value' => (int) ($counts[HealthCheckStatus::Warning->value] ?? 0)],
            ['key' => 'critical', 'label' => 'Critical', 'value' => (int) ($counts[HealthCheckStatus::Critical->value] ?? 0)],
            ['key' => 'unknown', 'label' => 'Unknown', 'value' => (int) ($counts[HealthCheckStatus::Unknown->value] ?? 0)],
            ['key' => 'avg_response_time_ms', 'label' => 'Avg Response Time (ms)', 'value' => round((float) $avgResponseTime, 2)],
        ];
    }
}
