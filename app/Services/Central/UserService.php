<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\UserRole;
use App\Models\Central\Permission;
use App\Models\Central\Role;
use App\Models\Central\User;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Central platform administrator records and queries.
 *
 * Encapsulates all business logic for user management, including
 * creation, updates, pagination, filtering, deletion, restoration,
 * Spatie role/permission sync, and KPI metrics.
 */
class UserService
{
    private const DEFAULT_GUARD = 'web';

    /**
     * Get paginated user records with eager loaded relations.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param mixed $isActive Active/inactive filter tokens.
     * @param mixed $trashed Soft-delete filter tokens (only, with).
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        mixed $isActive = null,
        mixed $trashed = null,
    ): LengthAwarePaginator {
        return User::query()
            ->with(['roles.permissions', 'permissions'])
            ->search($search)
            ->filterIsActive(QueryFilter::filterList($isActive))
            ->filterTrashed(QueryFilter::filterList($trashed))
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
            ->with(['roles.permissions', 'permissions'])
            ->findOrFail($id);
    }

    /**
     * Create a new user.
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

        return $user->fresh(['roles.permissions', 'permissions']);
    }

    /**
     * Update user.
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

        return $user->fresh(['roles.permissions', 'permissions']);
    }

    /**
     * Delete a single user.
     *
     * @param User $user The model instance to delete.
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Delete multiple users by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of deleted records.
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = User::query()->whereIn('id', $ids)->get();
            $deleted = 0;

            foreach ($records as $record) {
                if ($record->delete()) {
                    $deleted++;
                }
            }

            if ($deleted > 0) {
                $this->forgetPermissionCache();
            }

            return $deleted;
        });
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

        return $model->load(['roles.permissions', 'permissions']);
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

        return $user->fresh(['roles.permissions', 'permissions']);
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

        return $user->fresh(['roles.permissions', 'permissions']);
    }

    /**
     * Remove a single Spatie role from the user.
     *
     * @return User
     */
    public function detachRole(User $user, Role $role): User
    {
        $this->forgetPermissionCache();

        $user->removeRole($role);

        return $user->fresh(['roles.permissions', 'permissions']);
    }

    /**
     * Remove a single direct Spatie permission from the user.
     *
     * @return User
     */
    public function detachPermission(User $user, Permission $permission): User
    {
        $this->forgetPermissionCache();

        $user->revokePermissionTo($permission);

        return $user->fresh(['roles.permissions', 'permissions']);
    }

    /**
     * Update the user's last login timestamp.
     *
     * @param User $user The user who logged in.
     *
     * @return User
     */
    public function recordLogin(User $user): User
    {
        $user->update(['last_login_at' => now()]);

        return $user->fresh();
    }

    /**
     * Toggle the user's active flag.
     *
     * @param User $user The user whose status is toggled.
     *
     * @return User
     */
    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        return $user->fresh();
    }

    /**
     * KPI card metrics for users.
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
        $recentLogin = User::query()
            ->where('last_login_at', '>=', now()->subDays(7))
            ->count();

        $superAdmins = User::query()
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', UserRole::SuperAdmin->value);
            })
            ->count();

        return [
            ['key' => 'total', 'label' => 'Total Users', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'inactive', 'label' => 'Inactive', 'value' => $inactive],
            ['key' => 'verified', 'label' => 'Verified', 'value' => $verified],
            ['key' => 'with_roles', 'label' => 'With Roles', 'value' => $withRoles],
            ['key' => 'recent_login', 'label' => 'Recent Login', 'value' => $recentLogin],
            ['key' => 'super_admin', 'label' => 'Super Admins', 'value' => $superAdmins],
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
}
