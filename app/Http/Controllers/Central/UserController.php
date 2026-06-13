<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\BulkDeleteUsersRequest;
use App\Http\Requests\Central\StoreUserRequest;
use App\Http\Requests\Central\SyncUserPermissionsRequest;
use App\Http\Requests\Central\SyncUserRolesRequest;
use App\Http\Requests\Central\UpdateUserRequest;
use App\Http\Resources\Central\UserResource;
use App\Models\Central\Permission;
use App\Models\Central\Role;
use App\Models\Central\User;
use App\Services\Central\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Central platform administrator users.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the UserService layer.
 */
class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param UserService $service
     */
    public function __construct(
        private readonly UserService $service,
    ) {}

    /**
     * Get paginated user records.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = $request->query('is_active');
        $trashed = $request->query('trashed');

        $items = $this->service->getPaginated($perPage, $search, $isActive, $trashed);

        return $this->paginated($items, UserResource::collection($items), 'Users retrieved successfully.');
    }

    /**
     * KPI card metrics for users.
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'User KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new user.
     *
     * @param StoreUserRequest $request Validated request payload.
     *
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new UserResource($item), 'User created successfully.');
    }

    /**
     * Find user by route binding.
     *
     * @param User $user User instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $item = $this->service->findOrFail($user->id);

        return $this->success(new UserResource($item), 'User retrieved successfully.');
    }

    /**
     * Update user.
     *
     * @param UpdateUserRequest $request Validated request payload.
     * @param User $user User instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $item = $this->service->update($user, $request->validated());

        return $this->updated(new UserResource($item), 'User updated successfully.');
    }

    /**
     * Delete user.
     *
     * @param User $user User instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->service->delete($user);

        return $this->deleted('User deleted successfully.');
    }

    /**
     * Delete multiple users in one request.
     *
     * @param BulkDeleteUsersRequest $request
     *
     * @return JsonResponse
     */
    public function bulkDestroy(BulkDeleteUsersRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} user(s) deleted successfully.",
        );
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param int $id Trashed record identifier.
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $item = $this->service->restore($id);

        return $this->success(
            new UserResource($item),
            'User restored successfully.',
        );
    }

    /**
     * Restore multiple soft-deleted users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $restored = $this->service->restoreMany($ids);

        return $this->success(
            ['restored' => $restored],
            "{$restored} user(s) restored successfully.",
        );
    }

    /**
     * Update the user's last login timestamp.
     *
     * @param User $user User instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function recordLogin(User $user): JsonResponse
    {
        $item = $this->service->recordLogin($user);

        return $this->success(new UserResource($item), 'Login recorded.');
    }

    /**
     * Toggle the active status of a user.
     *
     * @param User $user User instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function toggleActive(User $user): JsonResponse
    {
        $item = $this->service->toggleActive($user);

        return $this->success(
            new UserResource($item),
            'User active status toggled successfully.',
        );
    }

    /**
     * Replace all Spatie roles assigned to the user.
     *
     * @param SyncUserRolesRequest $request Validated request payload.
     * @param User $user User instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function syncRoles(SyncUserRolesRequest $request, User $user): JsonResponse
    {
        $item = $this->service->syncRoles(
            $user,
            $request->validated('role_ids', []),
        );

        return $this->updated(new UserResource($item), 'User roles synced successfully.');
    }

    /**
     * Replace all direct Spatie permissions assigned to the user.
     *
     * @param SyncUserPermissionsRequest $request Validated request payload.
     * @param User $user User instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function syncPermissions(SyncUserPermissionsRequest $request, User $user): JsonResponse
    {
        $item = $this->service->syncPermissions(
            $user,
            $request->validated('permission_ids', []),
        );

        return $this->updated(new UserResource($item), 'User permissions synced successfully.');
    }

    /**
     * Remove a Spatie role from the user.
     *
     * @param User $user User instance resolved via route model binding.
     * @param Role $role Role instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function detachRole(User $user, Role $role): JsonResponse
    {
        $item = $this->service->detachRole($user, $role);

        return $this->updated(new UserResource($item), 'Role removed from user successfully.');
    }

    /**
     * Remove a direct Spatie permission from the user.
     *
     * @param User $user User instance resolved via route model binding.
     * @param Permission $permission Permission instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function detachPermission(User $user, Permission $permission): JsonResponse
    {
        $item = $this->service->detachPermission($user, $permission);

        return $this->updated(new UserResource($item), 'Permission removed from user successfully.');
    }
}
