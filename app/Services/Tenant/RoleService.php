<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Enums\Tenant\TenantUserRole;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tenant store role records and queries.
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
     * Spatie role names that cannot have permissions changed via the matrix.
     *
     * @var list<string>
     */
    private const SYSTEM_ROLE_NAMES = [
        TenantUserRole::StoreOwner->value,
    ];

    private const DEFAULT_GUARD = 'tenant';

    /**
     * Get all role records.
     *
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
     * Base query with role detail relations.
     *
     * @return Builder<Role>
     */
    private function queryWithDetails(): Builder
    {
        return Role::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Get paginated role records.
     *
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
     * Find role by ID or fail.
     */
    public function findOrFail(int $id): Role
    {
        return $this->queryWithDetails()->findOrFail($id);
    }

    /**
     * Create a new role.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    /**
     * Update role.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Delete role.
     */
    public function delete(Role $role): bool
    {
        if ($this->isSystemRole($role)) {
            return false;
        }

        $deleted = $role->delete();

        if ($deleted) {
            $this->forgetPermissionCache();
        }

        return $deleted;
    }

    /**
     * Delete multiple non-system roles by ID.
     *
     * @param  list<int>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $deleted = 0;

            Role::query()
                ->whereIn('id', $ids)
                ->whereNotIn('name', self::SYSTEM_ROLE_NAMES)
                ->get()
                ->each(function (Role $role) use (&$deleted): void {
                    if ($role->delete()) {
                        $deleted++;
                    }
                });

            if ($deleted > 0) {
                $this->forgetPermissionCache();
            }

            return $deleted;
        });
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
     * Bulk replace permissions for multiple roles from the matrix UI.
     *
     * @param  list<array{role_id: int, permission_ids: list<int>}>  $roles
     */
    public function syncPermissionsMatrix(array $roles): void
    {
        DB::transaction(function () use ($roles): void {
            $this->forgetPermissionCache();

            foreach ($roles as $entry) {
                $role = Role::query()->findOrFail($entry['role_id']);

                if ($this->isSystemRole($role)) {
                    continue;
                }

                $permissions = Permission::query()
                    ->whereIn('id', $entry['permission_ids'] ?? [])
                    ->where('guard_name', $role->guard_name)
                    ->pluck('id')
                    ->all();

                $role->syncPermissions($permissions);
            }
        });
    }

    /**
     * Determine whether the role is locked in the permissions matrix.
     */
    public function isSystemRole(Role $role): bool
    {
        return in_array($role->name, self::SYSTEM_ROLE_NAMES, true);
    }

    /**
     * Replace all permissions assigned to the role.
     *
     * @param  list<int>  $permissionIds
     */
    public function syncPermissions(Role $role, array $permissionIds): Role
    {
        if ($this->isSystemRole($role)) {
            return $role->fresh(self::DETAIL_RELATIONS);
        }

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
     * Role-permission matrix payload for the admin UI.
     *
     * @return array{
     *     guard_name: string,
     *     total_permissions: int,
     *     roles: list<array{
     *         id: int,
     *         name: string,
     *         guard_name: string,
     *         permission_ids: list<int>,
     *         is_system: bool
     *     }>,
     *     permission_groups: list<array{
     *         module: string,
     *         permissions: list<array{id: int, name: string, guard_name: string}>
     *     }>
     * }
     */
    public function getPermissionsMatrix(?string $guard = null): array
    {
        $guardName = $guard ?? self::DEFAULT_GUARD;

        $permissions = Permission::query()
            ->where('guard_name', $guardName)
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->where('guard_name', $guardName)
            ->with('permissions:id')
            ->orderBy('name')
            ->get();

        $permissionGroups = $permissions
            ->groupBy(fn (Permission $permission) => $permission->module ?? 'general')
            ->map(fn ($items, string $module) => [
                'module' => $module,
                'permissions' => $items
                    ->map(fn (Permission $permission) => [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name,
                    ])
                    ->values()
                    ->all(),
            ])
            ->sortBy('module')
            ->values()
            ->all();

        return [
            'guard_name' => $guardName,
            'total_permissions' => $permissions->count(),
            'roles' => $roles
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'permission_ids' => $role->permissions->pluck('id')->all(),
                    'is_system' => $this->isSystemRole($role),
                ])
                ->values()
                ->all(),
            'permission_groups' => $permissionGroups,
        ];
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
        $tenantGuard = Role::query()->where('guard_name', self::DEFAULT_GUARD)->count();

        return [
            ['key' => 'total', 'label' => 'Total Roles', 'value' => $total],
            ['key' => 'with_permissions', 'label' => 'With Permissions', 'value' => $withPermissions],
            ['key' => 'tenant_guard', 'label' => 'Tenant Guard', 'value' => $tenantGuard],
        ];
    }

    /**
     * Clear Spatie permission cache after role permission changes.
     */
    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
