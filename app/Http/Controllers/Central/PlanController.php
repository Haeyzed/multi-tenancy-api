<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\BulkDeletePlansRequest;
use App\Http\Requests\Central\StorePlanRequest;
use App\Http\Requests\Central\UpdatePlanRequest;
use App\Http\Resources\Central\PlanResource;
use App\Models\Central\Plan;
use App\Services\Central\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Subscription plans offered on the platform.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the PlanService layer.
 */
class PlanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PlanService $service
     */
    public function __construct(
        private readonly PlanService $service,
    ) {}

    /**
     * Get paginated plan records.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = $request->query('is_active');
        $isPublic = $request->query('is_public');

        $items = $this->service->getPaginated($perPage, $search, $isActive, $isPublic);

        return $this->paginated($items, PlanResource::collection($items), 'Plans retrieved successfully.');
    }

    /**
     * List active plans as value/label pairs for select inputs.
     *
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Plan options retrieved successfully.');
    }

    /**
     * KPI card metrics for plans.
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Plan KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new plan.
     *
     * @param StorePlanRequest $request Validated request payload.
     *
     * @return JsonResponse
     */
    public function store(StorePlanRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PlanResource($item), 'Plan created successfully.');
    }

    /**
     * Find plan by route binding.
     *
     * @param Plan $plan Plan instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(Plan $plan): JsonResponse
    {
        $item = $this->service->findOrFail($plan->id);

        return $this->success(new PlanResource($item), 'Plan retrieved successfully.');
    }

    /**
     * Update plan.
     *
     * @param UpdatePlanRequest $request Validated request payload.
     * @param Plan $plan Plan instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $item = $this->service->update($plan, $request->validated());

        return $this->updated(new PlanResource($item), 'Plan updated successfully.');
    }

    /**
     * Delete plan.
     *
     * @param Plan $plan Plan instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function destroy(Plan $plan): JsonResponse
    {
        $this->service->delete($plan);

        return $this->deleted('Plan deleted successfully.');
    }

    /**
     * Delete multiple plans in one request.
     *
     * @param BulkDeletePlansRequest $request
     *
     * @return JsonResponse
     */
    public function bulkDestroy(BulkDeletePlansRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} plan(s) deleted successfully.",
        );
    }

    /**
     * Restore a soft-deleted plan.
     *
     * @param int $id Trashed record identifier.
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $item = $this->service->restore($id);

        return $this->success(new PlanResource($item), 'Plan restored successfully.');
    }

    /**
     * Restore multiple soft-deleted plans.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $restored = $this->service->restoreMany($ids);

        return $this->success(
            ['restored' => $restored],
            "{$restored} plan(s) restored successfully.",
        );
    }
}
