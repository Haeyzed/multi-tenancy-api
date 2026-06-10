<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\TenantImpersonationToken;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Central TenantImpersonationToken records and queries.
 */
class TenantImpersonationTokenService
{
    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'tenant',
        'administrator',
    ];

    /**
     * Get all TenantImpersonationToken records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, TenantImpersonationToken>
     */
    public function getAll(?string $search = null): Collection
    {
        return TenantImpersonationToken::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated TenantImpersonationToken records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, TenantImpersonationToken>
     */
    /**
     * @param  list<string>  $status
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $status = [],
    ): LengthAwarePaginator {
        return TenantImpersonationToken::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterStatus($status)
            ->latest()
            ->paginate($perPage);
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
        $plainToken = null;

        if (empty($data['token'])) {
            $plainToken = Str::random(64);
            $data['token'] = $plainToken;
        }

        $token = TenantImpersonationToken::query()->create($data);

        if ($plainToken !== null) {
            $token->setAttribute('plain_token', $plainToken);
        }

        return $token->load(self::LIST_RELATIONS);
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

        return $tenantImpersonationToken->fresh(self::LIST_RELATIONS);
    }

    /**
     * Delete TenantImpersonationToken.
     *
     * @param  TenantImpersonationToken  $tenantImpersonationToken  The model instance to delete.
     */
    public function delete(TenantImpersonationToken $tenantImpersonationToken): bool
    {
        return $tenantImpersonationToken->delete();
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
        $impersonationToken->update(['used_at' => now()]);

        return $impersonationToken->fresh(self::LIST_RELATIONS);
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
