<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\ApiKey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Central ApiKey records and queries.
 */
class ApiKeyService
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
     * @param  list<string>  $isActive
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $isActive = [],
    ): LengthAwarePaginator {
        return ApiKey::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterIsActive($isActive)
            ->latest()
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
        $plainKey = null;

        if (empty($data['key_hash'])) {
            $plainKey = 'ak_live_'.Str::random(40);
            $data['key_hash'] = Hash::make($plainKey);
        }

        $apiKey = ApiKey::query()->create($data);

        if ($plainKey !== null) {
            $apiKey->setAttribute('plain_key', $plainKey);
        }

        return $apiKey->load(self::LIST_RELATIONS);
    }

    /**
     * Update ApiKey.
     *
     * @param  ApiKey  $apiKey  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(ApiKey $apiKey, array $data): ApiKey
    {
        unset($data['key_hash']);

        $apiKey->update($data);

        return $apiKey->fresh(self::LIST_RELATIONS);
    }

    /**
     * Delete ApiKey.
     *
     * @param  ApiKey  $apiKey  The model instance to delete.
     */
    public function delete(ApiKey $apiKey): bool
    {
        return (bool) $apiKey->delete();
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
        $apiKey->update(['last_used_at' => now()]);

        return $apiKey->fresh(self::LIST_RELATIONS);
    }

    /**
     * Revoke an API key by deactivating it.
     *
     * @param  ApiKey  $apiKey  The API key to revoke.
     */
    public function revoke(ApiKey $apiKey): ApiKey
    {
        $apiKey->update(['is_active' => false]);

        return $apiKey->fresh(self::LIST_RELATIONS);
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
