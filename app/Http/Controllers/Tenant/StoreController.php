<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteStoresRequest;
use App\Http\Requests\Tenant\StoreStoreRequest;
use App\Http\Requests\Tenant\UpdateStoreRequest;
use App\Http\Resources\Tenant\StoreResource;
use App\Models\Tenant\Store;
use App\Services\Tenant\StoreService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Stores operated by the tenant.
 */
class StoreController extends Controller
{
    public function __construct(
        private readonly StoreService $service,
    ) {}

    /**
     * Get paginated store records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = QueryFilter::parseList($request->query('is_active'));
        $types = QueryFilter::parseList($request->query('type'));

        $items = $this->service->getPaginated(
            $perPage,
            is_string($search) ? $search : null,
            $isActive,
            $types,
        );

        return $this->paginated($items, StoreResource::collection($items), 'Stores retrieved successfully.');
    }

    /**
     * List active stores as value/label pairs for select inputs.
     */
    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Store options retrieved successfully.');
    }

    /**
     * KPI card metrics for stores.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Store KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new store.
     *
     * @param  StoreStoreRequest  $request  Validated request payload.
     */
    public function store(StoreStoreRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new StoreResource($item), 'Store created successfully.');
    }

    /**
     * Find store by route binding.
     *
     * @param  Store  $store  Store instance.
     */
    public function show(Store $store): JsonResponse
    {
        $item = $this->service->findOrFail($store->id);

        return $this->success(new StoreResource($item), 'Store retrieved successfully.');
    }

    /**
     * Update store.
     *
     * @param  UpdateStoreRequest  $request  Validated request payload.
     * @param  Store  $store  Store instance.
     */
    public function update(UpdateStoreRequest $request, Store $store): JsonResponse
    {
        $item = $this->service->update($store, $request->validated());

        return $this->updated(new StoreResource($item), 'Store updated successfully.');
    }

    /**
     * Delete store.
     *
     * @param  Store  $store  Store instance.
     */
    public function destroy(Store $store): JsonResponse
    {
        $this->service->delete($store);

        return $this->deleted('Store deleted successfully.');
    }

    /**
     * Delete multiple stores in one request.
     */
    public function bulkDestroy(BulkDeleteStoresRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} store(s) deleted successfully.",
        );
    }

    /**
     * Toggle the store active flag.
     */
    public function toggleActive(Store $store): JsonResponse
    {
        $item = $this->service->toggleActive($store);

        return $this->success(new StoreResource($item), 'Store active status toggled.');
    }

    /**
     * Mark store as the primary storefront.
     */
    public function setPrimary(Store $store): JsonResponse
    {
        $item = $this->service->setPrimary($store);

        return $this->success(new StoreResource($item), 'Primary store updated successfully.');
    }
}
