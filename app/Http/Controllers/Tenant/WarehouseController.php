<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteWarehousesRequest;
use App\Http\Requests\Tenant\StoreWarehouseRequest;
use App\Http\Requests\Tenant\UpdateWarehouseRequest;
use App\Http\Resources\Tenant\WarehouseResource;
use App\Models\Tenant\Warehouse;
use App\Services\Tenant\WarehouseService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Inventory fulfillment warehouses for the tenant.
 */
class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseService $service,
    ) {}

    /**
     * Get paginated warehouse records.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = QueryFilter::parseList($request->query('is_active'));
        $storeId = $request->query('store_id');

        $items = $this->service->getPaginated(
            $perPage,
            is_string($search) ? $search : null,
            $isActive,
            is_string($storeId) ? $storeId : null,
        );

        return $this->paginated($items, WarehouseResource::collection($items), 'Warehouses retrieved successfully.');
    }

    /**
     * List active warehouses as value/label pairs for select inputs.
     */
    public function options(Request $request): JsonResponse
    {
        $storeId = $request->query('store_id');

        return $this->success(
            $this->service->getOptions(is_string($storeId) ? $storeId : null),
            'Warehouse options retrieved successfully.',
        );
    }

    /**
     * KPI card metrics for warehouses.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Warehouse KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new warehouse.
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new WarehouseResource($item->load('store')), 'Warehouse created successfully.');
    }

    /**
     * Find warehouse by route binding.
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        $item = $this->service->findOrFail($warehouse->id);

        return $this->success(new WarehouseResource($item), 'Warehouse retrieved successfully.');
    }

    /**
     * Update warehouse.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $item = $this->service->update($warehouse, $request->validated());

        return $this->updated(new WarehouseResource($item), 'Warehouse updated successfully.');
    }

    /**
     * Delete warehouse.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $this->service->delete($warehouse);

        return $this->deleted('Warehouse deleted successfully.');
    }

    /**
     * Delete multiple warehouses in one request.
     */
    public function bulkDestroy(BulkDeleteWarehousesRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} warehouse(s) deleted successfully.",
        );
    }

    /**
     * Toggle the warehouse active flag.
     */
    public function toggleActive(Warehouse $warehouse): JsonResponse
    {
        $item = $this->service->toggleActive($warehouse);

        return $this->success(new WarehouseResource($item), 'Warehouse active status toggled.');
    }
}
