<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\TenantImpersonationToken;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central TenantImpersonationToken records and queries.
 */
class TenantImpersonationTokenService
{
    /**
     * Get all TenantImpersonationToken records.
     *
     * @return Collection<int, TenantImpersonationToken>
     */
    public function getAll(): Collection
    {
        return TenantImpersonationToken::query()->get();
    }

    /**
     * Get paginated TenantImpersonationToken records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, TenantImpersonationToken>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return TenantImpersonationToken::query()->paginate($perPage);
    }

    /**
     * Find TenantImpersonationToken by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?TenantImpersonationToken
    {
        return TenantImpersonationToken::query()->find($id);
    }

    /**
     * Find TenantImpersonationToken by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): TenantImpersonationToken
    {
        return TenantImpersonationToken::query()->findOrFail($id);
    }

    /**
     * Create a new TenantImpersonationToken.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TenantImpersonationToken
    {
        return TenantImpersonationToken::query()->create($data);
    }

    /**
     * Update TenantImpersonationToken.
     *
     * @param  TenantImpersonationToken  $tenantImpersonationToken  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(TenantImpersonationToken $tenantImpersonationToken, array $data): TenantImpersonationToken
    {
        $tenantImpersonationToken->update($data);

        return $tenantImpersonationToken->fresh();
    }

    /**
     * Delete TenantImpersonationToken.
     *
     * @param  TenantImpersonationToken  $tenantImpersonationToken  The model instance to delete.
     */
    public function delete(TenantImpersonationToken $tenantImpersonationToken): bool
    {
        return $tenantImpersonationToken->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, TenantImpersonationToken>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return TenantImpersonationToken::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Mark an impersonation token as used.
     *
     * @param  TenantImpersonationToken  $impersonationToken  The token to consume.
     */
    public function markAsUsed(TenantImpersonationToken $impersonationToken): TenantImpersonationToken
    {
        $impersonationToken->query()->update(['used_at' => now()]);

        return $impersonationToken->fresh();
    }

    /**
     * Get valid (unused and not expired) tokens.
     *
     * @return Collection<int, TenantImpersonationToken>
     */
    public function getValid(): Collection
    {
        return TenantImpersonationToken::query()->whereNull('used_at')
            ->orderBy('expires_at', 'asc')
            ->get();
    }
}
