<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Enums\Tenant\TenantUserRole;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tenant store staff user records and queries.
 */
class UserService
{
    use DeletesManyRecords;

    private const DEFAULT_GUARD = 'tenant';

    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'roles.permissions',
        'permissions',
        'avatarMedia',
    ];

    /**
     * Base query with user detail relations.
     *
     * @return Builder<User>
     */
    private function queryWithDetails(): Builder
    {
        return User::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Get paginated user records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @param  list<string>  $isActive  Active/inactive filter tokens.
     * @return LengthAwarePaginator<int, User>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $isActive = [],
    ): LengthAwarePaginator {
        return $this->queryWithDetails()
            ->search($search)
            ->filterIsActive($isActive)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate($perPage);
    }

    /**
     * Find user by ID or fail.
     *
     * @param  string  $id  Record identifier.
     */
    public function findOrFail(string $id): User
    {
        return $this->queryWithDetails()->findOrFail($id);
    }

    /**
     * Create a new store user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        [$roleIds, $permissionIds, $attributes] = $this->extractAccessPayload($data);

        $user = User::query()->create($attributes);

        if ($roleIds !== null) {
            $this->syncRoles($user, $roleIds);
        }

        if ($permissionIds !== null) {
            $this->syncPermissions($user, $permissionIds);
        }

        return $user->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Update store user.
     *
     * @param  User  $user  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(User $user, array $data): User
    {
        [$roleIds, $permissionIds, $attributes] = $this->extractAccessPayload($data);

        if (array_key_exists('password', $attributes) && blank($attributes['password'])) {
            unset($attributes['password']);
        }

        if ($attributes !== []) {
            $user->update($attributes);
        }

        if ($roleIds !== null) {
            $this->syncRoles($user, $roleIds);
        }

        if ($permissionIds !== null) {
            $this->syncPermissions($user, $permissionIds);
        }

        return $user->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Delete user.
     *
     * @param  User  $user  The model instance to delete.
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Delete multiple users by ID.
     *
     * @param  list<string>  $ids
     */
    public function deleteMany(array $ids): int
    {
        $deleted = $this->deleteManyByIds(User::class, $ids);

        if ($deleted > 0) {
            $this->forgetPermissionCache();
        }

        return $deleted;
    }

    /**
     * Replace all Spatie roles assigned to the user.
     *
     * @param  list<int>  $roleIds
     */
    public function syncRoles(User $user, array $roleIds): User
    {
        $this->forgetPermissionCache();

        $roles = Role::query()
            ->whereIn('id', $roleIds)
            ->where('guard_name', self::DEFAULT_GUARD)
            ->get();

        $user->syncRoles($roles);

        return $user->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Replace all direct Spatie permissions assigned to the user.
     *
     * @param  list<int>  $permissionIds
     */
    public function syncPermissions(User $user, array $permissionIds): User
    {
        $this->forgetPermissionCache();

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->where('guard_name', self::DEFAULT_GUARD)
            ->pluck('id')
            ->all();

        $user->syncPermissions($permissions);

        return $user->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Remove a single Spatie role from the user.
     */
    public function detachRole(User $user, Role $role): User
    {
        $this->forgetPermissionCache();

        $user->removeRole($role);

        return $user->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Remove a single direct Spatie permission from the user.
     */
    public function detachPermission(User $user, Permission $permission): User
    {
        $this->forgetPermissionCache();

        $user->revokePermissionTo($permission);

        return $user->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Toggle the user's active flag.
     */
    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        return $user->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * KPI card metrics for store users.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = User::query()->count();
        $active = User::query()->where('is_active', true)->count();
        $inactive = User::query()->where('is_active', false)->count();
        $verified = User::query()->whereNotNull('email_verified_at')->count();
        $withRoles = User::query()->whereHas('roles')->count();
        $storeOwners = User::query()
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', TenantUserRole::StoreOwner->value);
            })
            ->count();

        return [
            ['key' => 'total', 'label' => 'Total Users', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'inactive', 'label' => 'Inactive', 'value' => $inactive],
            ['key' => 'verified', 'label' => 'Verified', 'value' => $verified],
            ['key' => 'with_roles', 'label' => 'With Roles', 'value' => $withRoles],
            ['key' => 'store_owners', 'label' => 'Store Owners', 'value' => $storeOwners],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: list<int>|null, 1: list<int>|null, 2: array<string, mixed>}
     */
    private function extractAccessPayload(array $data): array
    {
        $roleIds = null;
        $permissionIds = null;

        if (array_key_exists('role_ids', $data)) {
            $roleIds = $data['role_ids'];
            unset($data['role_ids']);
        }

        if (array_key_exists('permission_ids', $data)) {
            $permissionIds = $data['permission_ids'];
            unset($data['permission_ids']);
        }

        return [$roleIds, $permissionIds, $data];
    }

    /**
     * Clear Spatie permission cache after user access changes.
     */
    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
