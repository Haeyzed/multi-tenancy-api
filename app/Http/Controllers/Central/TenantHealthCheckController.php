<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantHealthCheckRequest;
use App\Http\Requests\Central\UpdateTenantHealthCheckRequest;
use App\Http\Resources\Central\TenantHealthCheckResource;
use App\Models\Central\TenantHealthCheck;
use App\Services\Central\TenantHealthCheckService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Health check results for tenants.
 */
class TenantHealthCheckController extends Controller
{
    public function __construct(
        private readonly TenantHealthCheckService $service,
    ) {}

    /**
     * Get paginated TenantHealthCheck records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $status = QueryFilter::parseList($request->query('status'));

        $items = $this->service->getPaginated($perPage, $search, $status);

        return $this->paginated($items, TenantHealthCheckResource::collection($items), 'Health checks retrieved successfully.');
    }

    /**
     * KPI card metrics for health checks.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Health check KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new TenantHealthCheck.
     *
     * @param  StoreTenantHealthCheckRequest  $request  Validated request payload.
     */
    public function store(StoreTenantHealthCheckRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new TenantHealthCheckResource($item), 'Health check created successfully.');
    }

    /**
     * Find TenantHealthCheck by route binding.
     *
     * @param  TenantHealthCheck  $healthCheck  TenantHealthCheck instance.
     */
    public function show(TenantHealthCheck $healthCheck): JsonResponse
    {
        return $this->success(new TenantHealthCheckResource($healthCheck), 'Health check retrieved successfully.');
    }

    /**
     * Update TenantHealthCheck.
     *
     * @param  UpdateTenantHealthCheckRequest  $request  Validated request payload.
     * @param  TenantHealthCheck  $healthCheck  TenantHealthCheck instance.
     */
    public function update(UpdateTenantHealthCheckRequest $request, TenantHealthCheck $healthCheck): JsonResponse
    {
        $item = $this->service->update($healthCheck, $request->validated());

        return $this->updated(new TenantHealthCheckResource($item), 'Health check updated successfully.');
    }

    /**
     * Delete TenantHealthCheck.
     *
     * @param  TenantHealthCheck  $healthCheck  TenantHealthCheck instance.
     */
    public function destroy(TenantHealthCheck $healthCheck): JsonResponse
    {
        $this->service->delete($healthCheck);

        return $this->deleted('Health check deleted successfully.');
    }
}
