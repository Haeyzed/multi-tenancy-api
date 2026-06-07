<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Permission;
use App\Models\Central\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\PermissionRegistrar;

/**
 * Central Role records and queries.
 */
class RoleService
{
    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'permissions',
    ];

    /**
     * Base query with role detail relations.
     *
     * @return Builder<Role>
     */
    private function queryWithDetails(): Builder
    {
        return Role::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Get all Role records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, Role>
     */
    public function getAll(?string $search = null): Collection
    {
        return $this->queryWithDetails()
            ->search($search)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get paginated Role records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, Role>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->queryWithDetails()
            ->search($search)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Find Role by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?Role
    {
        return $this->queryWithDetails()->find($id);
    }

    /**
     * Find Role by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): Role
    {
        return $this->queryWithDetails()->findOrFail($id);
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
        $role->update($data);

        return $role->fresh();
    }

    /**
     * Delete Role.
     *
     * @param  Role  $role  The model instance to delete.
     */
    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * Replace all permissions assigned to the role.
     *
     * @param  list<int>  $permissionIds
     */
    public function syncPermissions(Role $role, array $permissionIds): Role
    {
        $this->forgetPermissionCache();

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->where('guard_name', $role->guard_name)
            ->pluck('id')
            ->all();

        $role->syncPermissions($permissions);

        return $role->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Attach permissions to the role without removing existing ones.
     *
     * @param  list<int>  $permissionIds
     */
    public function attachPermissions(Role $role, array $permissionIds): Role
    {
        $this->forgetPermissionCache();

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->where('guard_name', $role->guard_name)
            ->get();

        $role->givePermissionTo($permissions);

        return $role->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Remove a single permission from the role.
     */
    public function detachPermission(Role $role, Permission $permission): Role
    {
        $this->forgetPermissionCache();

        $role->revokePermissionTo($permission);

        return $role->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Clear Spatie permission cache after role permission changes.
     */
    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * KPI card metrics for roles.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = Role::query()->count();
        $withPermissions = Role::query()->whereHas('permissions')->count();
        $webGuard = Role::query()->where('guard_name', 'web')->count();

        return [
            ['key' => 'total', 'label' => 'Total Roles', 'value' => $total],
            ['key' => 'with_permissions', 'label' => 'With Permissions', 'value' => $withPermissions],
            ['key' => 'web_guard', 'label' => 'Web Guard', 'value' => $webGuard],
        ];
    }
}
