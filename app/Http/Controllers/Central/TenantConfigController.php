<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantConfigRequest;
use App\Http\Requests\Central\UpdateTenantConfigRequest;
use App\Http\Resources\Central\TenantConfigResource;
use App\Models\Central\TenantConfig;
use App\Services\Central\TenantConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Per-tenant configuration key-value settings.
 */
class TenantConfigController extends Controller
{
    public function __construct(
        private readonly TenantConfigService $service,
    ) {}

    /**
     * Get paginated TenantConfig records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, TenantConfigResource::collection($items));
    }

    /**
     * Create a new TenantConfig.
     *
     * @param  StoreTenantConfigRequest  $request  Validated request payload.
     */
    public function store(StoreTenantConfigRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new TenantConfigResource($item), 'Tenant config created successfully.');
    }

    /**
     * Find TenantConfig by route binding.
     *
     * @param  TenantConfig  $tenantConfig  TenantConfig instance.
     */
    public function show(TenantConfig $tenantConfig): JsonResponse
    {
        return $this->success(new TenantConfigResource($tenantConfig));
    }

    /**
     * Update TenantConfig.
     *
     * @param  UpdateTenantConfigRequest  $request  Validated request payload.
     * @param  TenantConfig  $tenantConfig  TenantConfig instance.
     */
    public function update(UpdateTenantConfigRequest $request, TenantConfig $tenantConfig): JsonResponse
    {
        $item = $this->service->update($tenantConfig, $request->validated());

        return $this->updated(new TenantConfigResource($item), 'Tenant config updated successfully.');
    }

    /**
     * Delete TenantConfig.
     *
     * @param  TenantConfig  $tenantConfig  TenantConfig instance.
     */
    public function destroy(TenantConfig $tenantConfig): JsonResponse
    {
        $this->service->delete($tenantConfig);

        return $this->deleted('Tenant config deleted successfully.');
    }
}
