<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Permission records and queries.
 */
class PermissionService
{
    /**
     * Get all Permission records.
     *
     * @return Collection<int, Permission>
     */
    public function getAll(): Collection
    {
        return Permission::query()->get();
    }

    /**
     * Get paginated Permission records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, Permission>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Permission::query()->paginate($perPage);
    }

    /**
     * Find Permission by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?Permission
    {
        return Permission::query()->find($id);
    }

    /**
     * Find Permission by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): Permission
    {
        return Permission::query()->findOrFail($id);
    }

    /**
     * Create a new Permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Permission
    {
        return Permission::query()->create($data);
    }

    /**
     * Update Permission.
     *
     * @param  Permission  $permission  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Permission $permission, array $data): Permission
    {
        $permission->query()->update($data);

        return $permission->fresh();
    }

    /**
     * Delete Permission.
     *
     * @param  Permission  $permission  The model instance to delete.
     */
    public function delete(Permission $permission): bool
    {
        return $permission->query()->delete() > 0;
    }
}
