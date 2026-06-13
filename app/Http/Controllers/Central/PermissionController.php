<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\BulkDeletePermissionsRequest;
use App\Http\Requests\Central\StorePermissionRequest;
use App\Http\Requests\Central\UpdatePermissionRequest;
use App\Http\Resources\Central\PermissionResource;
use App\Models\Central\Permission;
use App\Services\Central\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spatie permission definitions for platform administrators.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the PermissionService layer.
 */
class PermissionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PermissionService $service
     */
    public function __construct(
        private readonly PermissionService $service,
    ) {}

    /**
     * Get paginated permission records.
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

        return $this->paginated($items, PermissionResource::collection($items), 'Permissions retrieved successfully.');
    }

    /**
     * KPI card metrics for permissions.
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Permission KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new permission.
     *
     * @param StorePermissionRequest $request Validated request payload.
     *
     * @return JsonResponse
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PermissionResource($item), 'Permission created successfully.');
    }

    /**
     * Find permission by route binding.
     *
     * @param Permission $permission Permission instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(Permission $permission): JsonResponse
    {
        $item = $this->service->findOrFail($permission->id);

        return $this->success(new PermissionResource($item), 'Permission retrieved successfully.');
    }

    /**
     * Update permission.
     *
     * @param UpdatePermissionRequest $request Validated request payload.
     * @param Permission $permission Permission instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $item = $this->service->update($permission, $request->validated());

        return $this->updated(new PermissionResource($item), 'Permission updated successfully.');
    }

    /**
     * Delete permission.
     *
     * @param Permission $permission Permission instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->service->delete($permission);

        return $this->deleted('Permission deleted successfully.');
    }

    /**
     * Delete multiple permissions in one request.
     *
     * @param BulkDeletePermissionsRequest $request
     *
     * @return JsonResponse
     */
    public function bulkDestroy(BulkDeletePermissionsRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} permission(s) deleted successfully.",
        );
    }
}
