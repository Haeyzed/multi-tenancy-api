<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantMetricRequest;
use App\Http\Requests\Central\UpdateTenantMetricRequest;
use App\Http\Resources\Central\TenantMetricResource;
use App\Models\Central\TenantMetric;
use App\Services\Central\TenantMetricService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Usage and performance metrics for tenants.
 */
class TenantMetricController extends Controller
{
    public function __construct(
        private readonly TenantMetricService $service,
    )
    {
    }

    /**
     * Get paginated TenantMetric records.
     *
     * @param Request $request Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $items = $this->service->getPaginated($perPage, $search, $startDate, $endDate);

        return $this->paginated($items, TenantMetricResource::collection($items), 'Tenant metrics retrieved successfully.');
    }

    /**
     * KPI card metrics for tenant usage and revenue.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Usage KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new TenantMetric.
     *
     * @param StoreTenantMetricRequest $request Validated request payload.
     */
    public function store(StoreTenantMetricRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new TenantMetricResource($item), 'Metric created successfully.');
    }

    /**
     * Find TenantMetric by route binding.
     *
     * @param TenantMetric $metric TenantMetric instance.
     */
    public function show(TenantMetric $metric): JsonResponse
    {
        return $this->success(
            new TenantMetricResource($metric->load(['tenant'])),
            'Tenant metric retrieved successfully.',
        );
    }

    /**
     * Update TenantMetric.
     *
     * @param UpdateTenantMetricRequest $request Validated request payload.
     * @param TenantMetric $metric TenantMetric instance.
     */
    public function update(UpdateTenantMetricRequest $request, TenantMetric $metric): JsonResponse
    {
        $item = $this->service->update($metric, $request->validated());

        return $this->updated(new TenantMetricResource($item), 'Metric updated successfully.');
    }

    /**
     * Delete TenantMetric.
     *
     * @param TenantMetric $metric TenantMetric instance.
     */
    public function destroy(TenantMetric $metric): JsonResponse
    {
        $this->service->delete($metric);

        return $this->deleted('Metric deleted successfully.');
    }
}
