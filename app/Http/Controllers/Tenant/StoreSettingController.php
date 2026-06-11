<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateStoreSettingRequest;
use App\Http\Resources\Tenant\StoreSettingResource;
use App\Models\Tenant\Store;
use App\Services\Tenant\StoreSettingService;
use Illuminate\Http\JsonResponse;

/**
 * Per-store operational settings.
 */
class StoreSettingController extends Controller
{
    public function __construct(
        private readonly StoreSettingService $service,
    ) {}

    /**
     * Get settings for a store.
     *
     * @param  Store  $store  Store instance.
     */
    public function show(Store $store): JsonResponse
    {
        $item = $this->service->getForStore($store);

        return $this->success(new StoreSettingResource($item), 'Store settings retrieved successfully.');
    }

    /**
     * Update settings for a store.
     *
     * @param  UpdateStoreSettingRequest  $request  Validated request payload.
     * @param  Store  $store  Store instance.
     */
    public function update(UpdateStoreSettingRequest $request, Store $store): JsonResponse
    {
        $item = $this->service->updateForStore($store, $request->validated());

        return $this->updated(new StoreSettingResource($item), 'Store settings updated successfully.');
    }
}
