<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\BulkDeleteTenantsRequest;
use App\Http\Requests\Central\StoreTenantRequest;
use App\Http\Requests\Central\UpdateTenantRequest;
use App\Http\Resources\Central\TenantResource;
use App\Models\Central\Tenant;
use App\Services\Central\PlanEntitlementService;
use App\Services\Central\TenantService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Multi-tenant platform tenants.
 */
class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService          $service,
        private readonly PlanEntitlementService $entitlements,
    )
    {
    }

    /**
     * Get paginated Tenant records.
     *
     * @param Request $request Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $status = QueryFilter::parseList($request->query('status'));

        $items = $this->service->getPaginated($perPage, $search, $status);

        return $this->paginated($items, TenantResource::collection($items), 'Tenants retrieved successfully.');
    }

    /**
     * List active tenants as value/label pairs for select inputs.
     */
    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Tenant options retrieved successfully.');
    }

    /**
     * KPI card metrics for tenants.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Tenant KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new Tenant.
     *
     * @param StoreTenantRequest $request Validated request payload.
     */
    public function store(StoreTenantRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new TenantResource($item), 'Tenant created successfully.');
    }

    /**
     * Find Tenant by route binding.
     *
     * @param Tenant $tenant Tenant instance.
     */
    public function show(Tenant $tenant): JsonResponse
    {
        return $this->success(new TenantResource($tenant), 'Tenant retrieved successfully.');
    }

    /**
     * Update Tenant.
     *
     * @param UpdateTenantRequest $request Validated request payload.
     * @param Tenant $tenant Tenant instance.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse
    {
        $item = $this->service->update($tenant, $request->validated());

        return $this->updated(new TenantResource($item), 'Tenant updated successfully.');
    }

    /**
     * Delete Tenant.
     *
     * @param Tenant $tenant Tenant instance.
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        $this->service->delete($tenant);

        return $this->deleted('Tenant deleted successfully.');
    }

    /**
     * Delete multiple tenants in one request.
     */
    public function bulkDestroy(BulkDeleteTenantsRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} tenant(s) deleted successfully.",
        );
    }

    /**
     * Get plan feature entitlements for a tenant.
     *
     * @param Tenant $tenant Tenant instance.
     */
    public function features(Tenant $tenant): JsonResponse
    {
        $tenant->loadMissing('plan');

        return $this->success([
            'tenant_id' => $tenant->id,
            'plan_id' => $tenant->plan_id,
            'entitlements' => $this->entitlements->all($tenant),
            'display_features' => $tenant->plan?->features,
        ], 'Tenant features retrieved successfully.');
    }

    /**
     * List tenants filtered by lifecycle status.
     *
     * @param string $status Tenant status (pending, active, suspended, cancelled).
     */
    public function getByStatus(string $status): JsonResponse
    {
        $items = $this->service->getByStatus($status);

        return $this->success(TenantResource::collection($items), 'Tenants retrieved successfully.');
    }

    /**
     * List tenants expiring within the given number of days.
     *
     * @param int $days Number of days ahead to check for expiration.
     */
    public function getExpiring(int $days): JsonResponse
    {
        $items = $this->service->getExpiring($days);

        return $this->success(TenantResource::collection($items), 'Expiring tenants retrieved successfully.');
    }

    /**
     * Restore a soft-deleted tenant.
     *
     * @param string $tenant UUID of the trashed tenant (no model binding; uses withTrashed).
     */
    public function restore(string $tenant): JsonResponse
    {
        $item = $this->service->restore($tenant);

        return $this->success(new TenantResource($item), 'Tenant restored successfully.');
    }

    /**
     * Permanently delete a tenant and its data.
     *
     * @param string $tenant UUID of the tenant to force delete (uses withTrashed).
     */
    public function forceDestroy(string $tenant): JsonResponse
    {
        $this->service->forceDelete($tenant);

        return $this->deleted('Tenant permanently deleted.');
    }
}
