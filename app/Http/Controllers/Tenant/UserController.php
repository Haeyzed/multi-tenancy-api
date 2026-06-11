<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteUsersRequest;
use App\Http\Requests\Tenant\StoreUserRequest;
use App\Http\Requests\Tenant\SyncUserPermissionsRequest;
use App\Http\Requests\Tenant\SyncUserRolesRequest;
use App\Http\Requests\Tenant\UpdateUserRequest;
use App\Http\Resources\Tenant\UserResource;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use App\Services\Tenant\UserService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Store staff users for the tenant.
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
    ) {}

    /**
     * Get paginated user records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = QueryFilter::parseList($request->query('is_active'));

        $items = $this->service->getPaginated($perPage, $search, $isActive);

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
     * Create a new user.
     *
     * @param  StoreUserRequest  $request  Validated request payload.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new UserResource($item), 'User created successfully.');
    }

    /**
     * Find user by route binding.
     *
     * @param  User  $user  User instance.
     */
    public function show(User $user): JsonResponse
    {
        $item = $this->service->findOrFail($user->id);

        return $this->success(new UserResource($item), 'User retrieved successfully.');
    }

    /**
     * Update user.
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
     * Delete user.
     *
     * @param  User  $user  User instance.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->service->delete($user);

        return $this->deleted('User deleted successfully.');
    }

    /**
     * Delete multiple users in one request.
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
     * Toggle the user's active flag.
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
        $item = $this->service->syncRoles($user, $request->validated('role_ids', []));

        return $this->updated(new UserResource($item), 'User roles synced successfully.');
    }

    /**
     * Replace all direct Spatie permissions assigned to the user.
     */
    public function syncPermissions(SyncUserPermissionsRequest $request, User $user): JsonResponse
    {
        $item = $this->service->syncPermissions($user, $request->validated('permission_ids', []));

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
