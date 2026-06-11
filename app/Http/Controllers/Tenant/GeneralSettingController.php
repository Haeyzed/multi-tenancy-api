<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateGeneralSettingRequest;
use App\Http\Resources\Tenant\GeneralSettingResource;
use App\Services\Tenant\GeneralSettingService;
use Illuminate\Http\JsonResponse;

/**
 * Tenant-wide general settings (company identity and defaults).
 */
class GeneralSettingController extends Controller
{
    public function __construct(
        private readonly GeneralSettingService $service,
    ) {}

    /**
     * Get tenant general settings.
     */
    public function show(): JsonResponse
    {
        return $this->success(
            new GeneralSettingResource($this->service->get()),
            'General settings retrieved successfully.',
        );
    }

    /**
     * KPI card metrics for general settings.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'General settings metrics retrieved successfully.',
        );
    }

    /**
     * Update tenant general settings.
     *
     * @param  UpdateGeneralSettingRequest  $request  Validated request payload.
     */
    public function update(UpdateGeneralSettingRequest $request): JsonResponse
    {
        $item = $this->service->update($request->validated());

        return $this->updated(new GeneralSettingResource($item), 'General settings updated successfully.');
    }
}
