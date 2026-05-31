<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePlanFeatureRequest;
use App\Http\Requests\Central\UpdatePlanFeatureRequest;
use App\Http\Resources\Central\PlanFeatureResource;
use App\Models\Central\PlanFeature;
use App\Services\Central\PlanFeatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Features included in subscription plans.
 */
class PlanFeatureController extends Controller
{
    public function __construct(
        private readonly PlanFeatureService $service,
    ) {}

    /**
     * Get paginated PlanFeature records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, PlanFeatureResource::collection($items));
    }

    /**
     * Create a new PlanFeature.
     *
     * @param  StorePlanFeatureRequest  $request  Validated request payload.
     */
    public function store(StorePlanFeatureRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PlanFeatureResource($item), 'Plan feature created successfully.');
    }

    /**
     * Find PlanFeature by route binding.
     *
     * @param  PlanFeature  $planFeature  PlanFeature instance.
     */
    public function show(PlanFeature $planFeature): JsonResponse
    {
        return $this->success(new PlanFeatureResource($planFeature));
    }

    /**
     * Update PlanFeature.
     *
     * @param  UpdatePlanFeatureRequest  $request  Validated request payload.
     * @param  PlanFeature  $planFeature  PlanFeature instance.
     */
    public function update(UpdatePlanFeatureRequest $request, PlanFeature $planFeature): JsonResponse
    {
        $item = $this->service->update($planFeature, $request->validated());

        return $this->updated(new PlanFeatureResource($item), 'Plan feature updated successfully.');
    }

    /**
     * Delete PlanFeature.
     *
     * @param  PlanFeature  $planFeature  PlanFeature instance.
     */
    public function destroy(PlanFeature $planFeature): JsonResponse
    {
        $this->service->delete($planFeature);

        return $this->deleted('Plan feature deleted successfully.');
    }
}
