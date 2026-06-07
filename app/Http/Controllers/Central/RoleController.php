<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\AttachRolePermissionsRequest;
use App\Http\Requests\Central\StoreRoleRequest;
use App\Http\Requests\Central\SyncRolePermissionsRequest;
use App\Http\Requests\Central\UpdateRoleRequest;
use App\Http\Resources\Central\RoleResource;
use App\Models\Central\Permission;
use App\Models\Central\Role;
use App\Services\Central\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spatie role definitions.
 */
class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $service,
    ) {}

    /**
     * Get paginated Role records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $items = $this->service->getPaginated($perPage, is_string($search) ? $search : null);

        return $this->paginated($items, RoleResource::collection($items), 'Roles retrieved successfully.');
    }

    /**
     * KPI card metrics for roles.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Role KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new Role.
     *
     * @param  StoreRoleRequest  $request  Validated request payload.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new RoleResource($item), 'Role created successfully.');
    }

    /**
     * Find Role by route binding.
     *
     * @param  Role  $role  Role instance.
     */
    public function show(Role $role): JsonResponse
    {
        $item = $this->service->findOrFail($role->id);

        return $this->success(new RoleResource($item), 'Role retrieved successfully.');
    }

    /**
     * Update Role.
     *
     * @param  UpdateRoleRequest  $request  Validated request payload.
     * @param  Role  $role  Role instance.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $item = $this->service->update($role, $request->validated());

        return $this->updated(new RoleResource($item), 'Role updated successfully.');
    }

    /**
     * Delete Role.
     *
     * @param  Role  $role  Role instance.
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->service->delete($role);

        return $this->deleted('Role deleted successfully.');
    }

    /**
     * Replace all permissions assigned to the role.
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
     */
    public function detachPermission(Role $role, Permission $permission): JsonResponse
    {
        $item = $this->service->detachPermission($role, $permission);

        return $this->updated(new RoleResource($item), 'Permission removed from role successfully.');
    }
}
