<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Enums\Tenant\TenantUserRole;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tenant store staff user records and queries.
 *
 * Encapsulates all business logic for user management, including
 * creation, updates, pagination, filtering, deletion, restoration,
 * role/permission sync, and KPI metrics.
 *
 * Deletion is blocked when the user is active.
 */
class UserService
{
    private const DEFAULT_GUARD = 'tenant';

    /**
     * Get paginated user records with eager loaded relations.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param mixed $isActive Active/inactive filter tokens.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        mixed $isActive = null,
    ): LengthAwarePaginator {
        return User::query()
            ->with(['roles.permissions', 'permissions', 'avatarMedia'])
            ->search($search)
            ->filterIsActive(QueryFilter::filterList($isActive))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate($perPage);
    }

    /**
     * Find user by ID or fail with eager loaded relations.
     *
     * @param int $id Record identifier.
     *
     * @return User
     */
    public function findOrFail(int $id): User
    {
        return User::query()
            ->with(['roles.permissions', 'permissions', 'avatarMedia'])
            ->findOrFail($id);
    }

    /**
     * Create a new store user.
     *
     * @param array<string, mixed> $data
     *
     * @return User
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

        return $user->fresh(['roles.permissions', 'permissions', 'avatarMedia']);
    }

    /**
     * Update store user.
     *
     * @param User $user The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     *
     * @return User
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

        return $user->fresh(['roles.permissions', 'permissions', 'avatarMedia']);
    }

    /**
     * Delete a single user.
     *
     * Deletion is blocked if the user is active.
     *
     * @param User $user The model instance to delete.
     *
     * @return bool
     *
     * @throws ValidationException
     */
    public function delete(User $user): bool
    {
        $this->assertUserNotActive($user);

        $deleted = $user->delete();

        if ($deleted) {
            $this->forgetPermissionCache();
        }

        return $deleted;
    }

    /**
     * Delete multiple users by ID.
     *
     * Deletion is blocked for any active users in the set.
     *
     * @param list<int> $ids
     *
     * @return int Number of deleted records.
     *
     * @throws ValidationException
     */
    public function deleteMany(array $ids): int
    {
        $this->assertUsersNotActive($ids);

        $deleted = DB::transaction(function () use ($ids): int {
            $records = User::query()->whereIn('id', $ids)->get();
            $count = 0;

            foreach ($records as $record) {
                if ($record->delete()) {
                    $count++;
                }
            }

            return $count;
        });

        if ($deleted > 0) {
            $this->forgetPermissionCache();
        }

        return $deleted;
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param int $id Trashed record identifier.
     *
     * @return User
     */
    public function restore(int $id): User
    {
        $model = User::withTrashed()->findOrFail($id);
        $model->restore();

        return $model->load(['roles.permissions', 'permissions', 'avatarMedia']);
    }

    /**
     * Restore multiple soft-deleted users by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of restored records.
     */
    public function restoreMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = User::withTrashed()->whereIn('id', $ids)->get();
            $restored = 0;

            foreach ($records as $record) {
                if ($record->restore()) {
                    $restored++;
                }
            }

            return $restored;
        });
    }

    /**
     * Toggle the active status of a user.
     *
     * @param User $user The model instance to toggle.
     *
     * @return User
     */
    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        return $user->fresh(['roles.permissions', 'permissions', 'avatarMedia']);
    }

    /**
     * Replace all Spatie roles assigned to the user.
     *
     * @param list<int> $roleIds
     *
     * @return User
     */
    public function syncRoles(User $user, array $roleIds): User
    {
        $this->forgetPermissionCache();

        $roles = Role::query()
            ->whereIn('id', $roleIds)
            ->where('guard_name', self::DEFAULT_GUARD)
            ->get();

        $user->syncRoles($roles);

        return $user->fresh(['roles.permissions', 'permissions', 'avatarMedia']);
    }

    /**
     * Replace all direct Spatie permissions assigned to the user.
     *
     * @param list<int> $permissionIds
     *
     * @return User
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

        return $user->fresh(['roles.permissions', 'permissions', 'avatarMedia']);
    }

    /**
     * Remove a single Spatie role from the user.
     *
     * @param User $user The model instance to update.
     * @param Role $role The role to detach.
     *
     * @return User
     */
    public function detachRole(User $user, Role $role): User
    {
        $this->forgetPermissionCache();

        $user->removeRole($role);

        return $user->fresh(['roles.permissions', 'permissions', 'avatarMedia']);
    }

    /**
     * Remove a single direct Spatie permission from the user.
     *
     * @param User $user The model instance to update.
     * @param Permission $permission The permission to revoke.
     *
     * @return User
     */
    public function detachPermission(User $user, Permission $permission): User
    {
        $this->forgetPermissionCache();

        $user->revokePermissionTo($permission);

        return $user->fresh(['roles.permissions', 'permissions', 'avatarMedia']);
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
     * @param array<string, mixed> $data
     *
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

    /**
     * Ensure a user is not active before deletion.
     *
     * @param User $user
     *
     * @throws ValidationException
     */
    private function assertUserNotActive(User $user): void
    {
        if (! $user->is_active) {
            return;
        }

        throw ValidationException::withMessages([
            'user' => [
                "Cannot delete \"{$user->name}\" because they are currently active. Deactivate them first.",
            ],
        ]);
    }

    /**
     * Ensure none of the given users are active.
     *
     * @param list<int> $ids
     *
     * @throws ValidationException
     */
    private function assertUsersNotActive(array $ids): void
    {
        $activeUsers = User::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get();

        if ($activeUsers->isEmpty()) {
            return;
        }

        $names = $activeUsers
            ->map(static fn (User $user): string => "\"{$user->name}\"")
            ->implode(', ');

        throw ValidationException::withMessages([
            'ids' => [
                "Cannot delete active users: {$names}. Deactivate them first.",
            ],
        ]);
    }
}
