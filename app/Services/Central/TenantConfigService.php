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
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'tenant',
    ];

    /**
     * Get all TenantConfig records.
     *
     * @param string|null $search Optional search term.
     * @return Collection<int, TenantConfig>
     */
    public function getAll(?string $search = null): Collection
    {
        return TenantConfig::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated TenantConfig records.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @return LengthAwarePaginator<int, TenantConfig>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return TenantConfig::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find TenantConfig by ID.
     *
     * @param int $id Record identifier.
     */
    public function find(int $id): ?TenantConfig
    {
        return TenantConfig::query()->find($id);
    }

    /**
     * Find TenantConfig by ID or fail.
     *
     * @param int $id Record identifier.
     */
    public function findOrFail(int $id): TenantConfig
    {
        return TenantConfig::query()->findOrFail($id);
    }

    /**
     * Create a new TenantConfig.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): TenantConfig
    {
        return TenantConfig::query()->create($data);
    }

    /**
     * Update TenantConfig.
     *
     * @param TenantConfig $tenantConfig The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(TenantConfig $tenantConfig, array $data): TenantConfig
    {
        $tenantConfig->update($data);

        return $tenantConfig->fresh(self::LIST_RELATIONS);
    }

    /**
     * Delete TenantConfig.
     *
     * @param TenantConfig $tenantConfig The model instance to delete.
     */
    public function delete(TenantConfig $tenantConfig): bool
    {
        return (bool)$tenantConfig->delete();
    }

    /**
     * Filter by tenant.
     *
     * @param string $tenantId Tenant UUID.
     * @return Collection<int, TenantConfig>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return TenantConfig::query()->where('tenant_id', $tenantId)->get();
    }
}
