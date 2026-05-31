<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePlanRequest;
use App\Http\Requests\Central\UpdatePlanRequest;
use App\Http\Resources\Central\PlanResource;
use App\Models\Central\Plan;
use App\Services\Central\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Subscription plans offered on the platform.
 */
class PlanController extends Controller
{
    public function __construct(
        private readonly PlanService $service,
    ) {}

    /**
     * Get paginated Plan records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, PlanResource::collection($items));
    }

    /**
     * Create a new Plan.
     *
     * @param  StorePlanRequest  $request  Validated request payload.
     */
    public function store(StorePlanRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PlanResource($item), 'Plan created successfully.');
    }

    /**
     * Find Plan by route binding.
     *
     * @param  Plan  $plan  Plan instance.
     */
    public function show(Plan $plan): JsonResponse
    {
        return $this->success(new PlanResource($plan));
    }

    /**
     * Update Plan.
     *
     * @param  UpdatePlanRequest  $request  Validated request payload.
     * @param  Plan  $plan  Plan instance.
     */
    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $item = $this->service->update($plan, $request->validated());

        return $this->updated(new PlanResource($item), 'Plan updated successfully.');
    }

    /**
     * Delete Plan.
     *
     * @param  Plan  $plan  Plan instance.
     */
    public function destroy(Plan $plan): JsonResponse
    {
        $this->service->delete($plan);

        return $this->deleted('Plan deleted successfully.');
    }
}
