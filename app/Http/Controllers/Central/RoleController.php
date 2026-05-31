<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreRoleRequest;
use App\Http\Requests\Central\UpdateRoleRequest;
use App\Http\Resources\Central\RoleResource;
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
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, RoleResource::collection($items));
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
        return $this->success(new RoleResource($role));
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
}
