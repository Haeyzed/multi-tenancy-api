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
     * Get all Domain records.
     *
     * @return Collection<int, Domain>
     */
    public function getAll(): Collection
    {
        return Domain::query()->get();
    }

    /**
     * Get paginated Domain records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, Domain>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Domain::query()->paginate($perPage);
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
        $domain->query()->update($data);

        return $domain->fresh();
    }

    /**
     * Delete Domain.
     *
     * @param  Domain  $domain  The model instance to delete.
     */
    public function delete(Domain $domain): bool
    {
        return $domain->query()->delete() > 0;
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
        $domain->query()->update(['is_primary' => true]);

        return $domain->fresh();
    }

    /**
     * Mark the domain as verified.
     *
     * @param  Domain  $domain  The domain to verify.
     */
    public function verify(Domain $domain): Domain
    {
        $domain->query()->update(['verified' => true]);

        return $domain->fresh();
    }
}
