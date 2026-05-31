<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central User records and queries.
 */
class UserService
{
    /**
     * Get all User records.
     *
     * @return Collection<int, User>
     */
    public function getAll(): Collection
    {
        return User::query()->get();
    }

    /**
     * Get paginated User records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, User>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()->paginate($perPage);
    }

    /**
     * Find User by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?User
    {
        return User::query()->find($id);
    }

    /**
     * Find User by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    /**
     * Create a new User.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    /**
     * Update User.
     *
     * @param  User  $user  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(User $user, array $data): User
    {
        $user->query()->update($data);

        return $user->fresh();
    }

    /**
     * Delete User.
     *
     * @param  User  $user  The model instance to delete.
     */
    public function delete(User $user): bool
    {
        return $user->query()->delete() > 0;
    }

    /**
     * Restore soft-deleted User.
     *
     * @param  int  $id  Trashed record identifier.
     */
    public function restore(int $id): User
    {
        $model = User::withTrashed()->findOrFail($id);
        $model->query()->restore();

        return $model;
    }

    /**
     * Force delete User.
     *
     * @param  int  $id  Trashed record identifier.
     */
    public function forceDelete(int $id): bool
    {
        $model = User::withTrashed()->findOrFail($id);

        return $model->query()->forceDelete() > 0;
    }

    /**
     * Get only active records.
     *
     * @return Collection<int, User>
     */
    public function getActive(): Collection
    {
        return User::query()->where('is_active', true)->get();
    }

    /**
     * Update the user's last login timestamp.
     *
     * @param  User  $user  The user who logged in.
     */
    public function recordLogin(User $user): User
    {
        $user->query()->update(['last_login_at' => now()]);

        return $user->fresh();
    }

    /**
     * Toggle the user's active flag.
     *
     * @param  User  $user  The user whose status is toggled.
     */
    public function toggleActive(User $user): User
    {
        $user->query()->update(['is_active' => ! $user->is_active]);

        return $user->fresh();
    }
}
