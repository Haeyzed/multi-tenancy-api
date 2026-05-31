<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\TenantConfig;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central TenantConfig records and queries.
 */
class TenantConfigService
{
    /**
     * Get all TenantConfig records.
     *
     * @return Collection<int, TenantConfig>
     */
    public function getAll(): Collection
    {
        return TenantConfig::query()->get();
    }

    /**
     * Get paginated TenantConfig records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, TenantConfig>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return TenantConfig::query()->paginate($perPage);
    }

    /**
     * Find TenantConfig by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?TenantConfig
    {
        return TenantConfig::query()->find($id);
    }

    /**
     * Find TenantConfig by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): TenantConfig
    {
        return TenantConfig::query()->findOrFail($id);
    }

    /**
     * Create a new TenantConfig.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TenantConfig
    {
        return TenantConfig::query()->create($data);
    }

    /**
     * Update TenantConfig.
     *
     * @param  TenantConfig  $tenantConfig  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(TenantConfig $tenantConfig, array $data): TenantConfig
    {
        $tenantConfig->query()->update($data);

        return $tenantConfig->fresh();
    }

    /**
     * Delete TenantConfig.
     *
     * @param  TenantConfig  $tenantConfig  The model instance to delete.
     */
    public function delete(TenantConfig $tenantConfig): bool
    {
        return $tenantConfig->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, TenantConfig>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return TenantConfig::query()->where('tenant_id', $tenantId)->get();
    }
}
