<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Role records and queries.
 */
class RoleService
{
    /**
     * Get all Role records.
     *
     * @return Collection<int, Role>
     */
    public function getAll(): Collection
    {
        return Role::query()->get();
    }

    /**
     * Get paginated Role records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, Role>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Role::query()->paginate($perPage);
    }

    /**
     * Find Role by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?Role
    {
        return Role::query()->find($id);
    }

    /**
     * Find Role by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): Role
    {
        return Role::query()->findOrFail($id);
    }

    /**
     * Create a new Role.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    /**
     * Update Role.
     *
     * @param  Role  $role  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Role $role, array $data): Role
    {
        $role->query()->update($data);

        return $role->fresh();
    }

    /**
     * Delete Role.
     *
     * @param  Role  $role  The model instance to delete.
     */
    public function delete(Role $role): bool
    {
        return $role->query()->delete() > 0;
    }
}
