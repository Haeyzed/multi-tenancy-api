<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\ApiKey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central ApiKey records and queries.
 */
class ApiKeyService
{
    /**
     * Get all ApiKey records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, ApiKey>
     */
    public function getAll(?string $search = null): Collection
    {
        return ApiKey::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated ApiKey records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, ApiKey>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return ApiKey::query()
            ->forTenant()
            ->search($search)
            ->paginate($perPage);
    }

    /**
     * Find ApiKey by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?ApiKey
    {
        return ApiKey::query()->find($id);
    }

    /**
     * Find ApiKey by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): ApiKey
    {
        return ApiKey::query()->findOrFail($id);
    }

    /**
     * Create a new ApiKey.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ApiKey
    {
        return ApiKey::query()->create($data);
    }

    /**
     * Update ApiKey.
     *
     * @param  ApiKey  $apiKey  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(ApiKey $apiKey, array $data): ApiKey
    {
        $apiKey->query()->update($data);

        return $apiKey->fresh();
    }

    /**
     * Delete ApiKey.
     *
     * @param  ApiKey  $apiKey  The model instance to delete.
     */
    public function delete(ApiKey $apiKey): bool
    {
        return $apiKey->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, ApiKey>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return ApiKey::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Get only active records.
     *
     * @return Collection<int, ApiKey>
     */
    public function getActive(): Collection
    {
        return ApiKey::query()->where('is_active', true)->get();
    }

    /**
     * Record API key usage by updating last_used_at.
     *
     * @param  ApiKey  $apiKey  The API key that was used.
     */
    public function recordUsage(ApiKey $apiKey): ApiKey
    {
        $apiKey->query()->update(['last_used_at' => now()]);

        return $apiKey->fresh();
    }

    /**
     * Revoke an API key by deactivating it.
     *
     * @param  ApiKey  $apiKey  The API key to revoke.
     */
    public function revoke(ApiKey $apiKey): ApiKey
    {
        $apiKey->query()->update(['is_active' => false]);

        return $apiKey->fresh();
    }

    /**
     * KPI card metrics for API keys.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $query = ApiKey::query()->forTenant();

        $total = (clone $query)->count();
        $active = (clone $query)->where('is_active', true)->count();
        $expired = (clone $query)
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->count();

        return [
            ['key' => 'total', 'label' => 'Total API Keys', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'inactive', 'label' => 'Inactive', 'value' => $total - $active],
            ['key' => 'expired', 'label' => 'Expired', 'value' => $expired],
        ];
    }
}
