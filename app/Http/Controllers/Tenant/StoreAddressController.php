<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteStoreAddressesRequest;
use App\Http\Requests\Tenant\StoreStoreAddressRequest;
use App\Http\Requests\Tenant\UpdateStoreAddressRequest;
use App\Http\Resources\Tenant\StoreAddressResource;
use App\Models\Tenant\Store;
use App\Models\Tenant\StoreAddress;
use App\Services\Tenant\StoreAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Addresses belonging to a store.
 */
class StoreAddressController extends Controller
{
    public function __construct(
        private readonly StoreAddressService $service,
    ) {}

    /**
     * List addresses for a store.
     */
    public function index(Request $request, Store $store): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');

        $items = $this->service->getPaginatedForStore(
            $store,
            $perPage,
            is_string($search) ? $search : null,
        );

        return $this->paginated($items, StoreAddressResource::collection($items), 'Store addresses retrieved successfully.');
    }

    /**
     * Create an address for a store.
     */
    public function store(StoreStoreAddressRequest $request, Store $store): JsonResponse
    {
        $item = $this->service->create($store, $request->validated());

        return $this->created(new StoreAddressResource($item), 'Store address created successfully.');
    }

    /**
     * Show a store address.
     */
    public function show(Store $store, StoreAddress $address): JsonResponse
    {
        $item = $this->service->findForStoreOrFail($store, $address->id);

        return $this->success(new StoreAddressResource($item), 'Store address retrieved successfully.');
    }

    /**
     * Update a store address.
     */
    public function update(UpdateStoreAddressRequest $request, Store $store, StoreAddress $address): JsonResponse
    {
        $item = $this->service->findForStoreOrFail($store, $address->id);
        $updated = $this->service->update($item, $request->validated());

        return $this->updated(new StoreAddressResource($updated), 'Store address updated successfully.');
    }

    /**
     * Delete a store address.
     */
    public function destroy(Store $store, StoreAddress $address): JsonResponse
    {
        $item = $this->service->findForStoreOrFail($store, $address->id);
        $this->service->delete($item);

        return $this->deleted('Store address deleted successfully.');
    }

    /**
     * Delete multiple store addresses.
     */
    public function bulkDestroy(BulkDeleteStoreAddressesRequest $request, Store $store): JsonResponse
    {
        $deleted = $this->service->deleteManyForStore($store, $request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} store address(es) deleted successfully.",
        );
    }

    /**
     * Mark an address as the default for its store.
     */
    public function setDefault(Store $store, StoreAddress $address): JsonResponse
    {
        $item = $this->service->findForStoreOrFail($store, $address->id);
        $updated = $this->service->setDefault($item);

        return $this->success(new StoreAddressResource($updated), 'Default store address updated successfully.');
    }
}
