<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
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
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
    ) {}

    /**
     * Get paginated User records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');

        $items = $this->service->getPaginated($perPage, $search);

        return $this->paginated($items, UserResource::collection($items), 'Users retrieved successfully.');
    }

    /**
     * KPI card metrics for users.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'User KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new User.
     *
     * @param  StoreUserRequest  $request  Validated request payload.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new UserResource($item), 'User created successfully.');
    }

    /**
     * Find User by route binding.
     *
     * @param  User  $user  User instance.
     */
    public function show(User $user): JsonResponse
    {
        $item = $this->service->findOrFail($user->id);

        return $this->success(new UserResource($item), 'User retrieved successfully.');
    }

    /**
     * Update User.
     *
     * @param  UpdateUserRequest  $request  Validated request payload.
     * @param  User  $user  User instance.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $item = $this->service->update($user, $request->validated());

        return $this->updated(new UserResource($item), 'User updated successfully.');
    }

    /**
     * Delete User.
     *
     * @param  User  $user  User instance.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->service->delete($user);

        return $this->deleted('User deleted successfully.');
    }

    /**
     * Update the user's last login timestamp.
     *
     * @param  User  $user  User instance.
     */
    public function recordLogin(User $user): JsonResponse
    {
        $item = $this->service->recordLogin($user);

        return $this->success(new UserResource($item), 'Login recorded.');
    }

    /**
     * Toggle the user's active flag.
     *
     * @param  User  $user  User instance.
     */
    public function toggleActive(User $user): JsonResponse
    {
        $item = $this->service->toggleActive($user);

        return $this->success(new UserResource($item), 'User active status toggled.');
    }

    /**
     * Replace all Spatie roles assigned to the user.
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
     */
    public function detachRole(User $user, Role $role): JsonResponse
    {
        $item = $this->service->detachRole($user, $role);

        return $this->updated(new UserResource($item), 'Role removed from user successfully.');
    }

    /**
     * Remove a direct Spatie permission from the user.
     */
    public function detachPermission(User $user, Permission $permission): JsonResponse
    {
        $item = $this->service->detachPermission($user, $permission);

        return $this->updated(new UserResource($item), 'Permission removed from user successfully.');
    }
}
