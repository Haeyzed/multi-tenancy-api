<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\AttachRolePermissionsRequest;
use App\Http\Requests\Central\BulkDeleteRolesRequest;
use App\Http\Requests\Central\StoreRoleRequest;
use App\Http\Requests\Central\SyncRolePermissionsMatrixRequest;
use App\Http\Requests\Central\SyncRolePermissionsRequest;
use App\Http\Requests\Central\UpdateRoleRequest;
use App\Http\Resources\Central\RoleResource;
use App\Models\Central\Permission;
use App\Models\Central\Role;
use App\Services\Central\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spatie role definitions for platform administrators.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the RoleService layer.
 */
class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param RoleService $service
     */
    public function __construct(
        private readonly RoleService $service,
    ) {}

    /**
     * Get paginated role records.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');

        $items = $this->service->getPaginated($perPage, $search);

        return $this->paginated($items, RoleResource::collection($items), 'Roles retrieved successfully.');
    }

    /**
     * KPI card metrics for roles.
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Role KPI metrics retrieved successfully.',
        );
    }

    /**
     * Role-permission matrix for the admin UI.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return JsonResponse
     */
    public function permissionsMatrix(Request $request): JsonResponse
    {
        $guard = $request->query('guard');

        return $this->success(
            $this->service->getPermissionsMatrix(is_string($guard) ? $guard : null),
            'Role permissions matrix retrieved successfully.',
        );
    }

    /**
     * Bulk sync role permissions from the matrix UI.
     *
     * @param SyncRolePermissionsMatrixRequest $request Validated request payload.
     *
     * @return JsonResponse
     */
    public function syncPermissionsMatrix(SyncRolePermissionsMatrixRequest $request): JsonResponse
    {
        $this->service->syncPermissionsMatrix($request->validated('roles'));

        return $this->success(
            $this->service->getPermissionsMatrix(),
            'Role permissions matrix synced successfully.',
        );
    }

    /**
     * Create a new role.
     *
     * @param StoreRoleRequest $request Validated request payload.
     *
     * @return JsonResponse
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new RoleResource($item), 'Role created successfully.');
    }

    /**
     * Find role by route binding.
     *
     * @param Role $role Role instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(Role $role): JsonResponse
    {
        $item = $this->service->findOrFail($role->id);

        return $this->success(new RoleResource($item), 'Role retrieved successfully.');
    }

    /**
     * Update role.
     *
     * @param UpdateRoleRequest $request Validated request payload.
     * @param Role $role Role instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $item = $this->service->update($role, $request->validated());

        return $this->updated(new RoleResource($item), 'Role updated successfully.');
    }

    /**
     * Delete role.
     *
     * @param Role $role Role instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->service->delete($role);

        return $this->deleted('Role deleted successfully.');
    }

    /**
     * Delete multiple roles in one request.
     *
     * @param BulkDeleteRolesRequest $request
     *
     * @return JsonResponse
     */
    public function bulkDestroy(BulkDeleteRolesRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} role(s) deleted successfully.",
        );
    }

    /**
     * Replace all permissions assigned to the role.
     *
     * @param SyncRolePermissionsRequest $request Validated request payload.
     * @param Role $role Role instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $item = $this->service->syncPermissions(
            $role,
            $request->validated('permission_ids', []),
        );

        return $this->updated(new RoleResource($item), 'Role permissions synced successfully.');
    }

    /**
     * Attach permissions to the role without removing existing ones.
     *
     * @param AttachRolePermissionsRequest $request Validated request payload.
     * @param Role $role Role instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function attachPermissions(AttachRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $item = $this->service->attachPermissions(
            $role,
            $request->validated('permission_ids'),
        );

        return $this->updated(new RoleResource($item), 'Permissions attached to role successfully.');
    }

    /**
     * Remove a permission from the role.
     *
     * @param Role $role Role instance resolved via route model binding.
     * @param Permission $permission Permission instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function detachPermission(Role $role, Permission $permission): JsonResponse
    {
        $item = $this->service->detachPermission($role, $permission);

        return $this->updated(new RoleResource($item), 'Permission removed from role successfully.');
    }
}
