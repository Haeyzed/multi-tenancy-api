<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Domain;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Domain records and queries.
 */
class DomainService
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
     * Get all Domain records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, Domain>
     */
    public function getAll(?string $search = null): Collection
    {
        return Domain::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated Domain records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, Domain>
     */
    /**
     * @param  list<string>  $verified
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $verified = [],
    ): LengthAwarePaginator {
        return Domain::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterVerified($verified)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find Domain by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?Domain
    {
        return Domain::query()->find($id);
    }

    /**
     * Find Domain by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): Domain
    {
        return Domain::query()->findOrFail($id);
    }

    /**
     * Create a new Domain.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Domain
    {
        return Domain::query()->create($data);
    }

    /**
     * Update Domain.
     *
     * @param  Domain  $domain  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Domain $domain, array $data): Domain
    {
        $domain->update($data);

        return $domain->fresh(self::LIST_RELATIONS);
    }

    /**
     * Delete Domain.
     *
     * @param  Domain  $domain  The model instance to delete.
     */
    public function delete(Domain $domain): bool
    {
        return (bool) $domain->delete();
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, Domain>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return Domain::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Set the domain as the tenant's primary domain.
     *
     * @param  Domain  $domain  The domain to promote.
     */
    public function setPrimary(Domain $domain): Domain
    {
        Domain::query()->where('tenant_id', $domain->tenant_id)
            ->where('id', '!=', $domain->id)
            ->update(['is_primary' => false]);
        $domain->update(['is_primary' => true]);

        return $domain->fresh(self::LIST_RELATIONS);
    }

    /**
     * Mark the domain as verified.
     *
     * @param  Domain  $domain  The domain to verify.
     */
    public function verify(Domain $domain): Domain
    {
        $domain->update(['verified' => true]);

        return $domain->fresh(self::LIST_RELATIONS);
    }
}
