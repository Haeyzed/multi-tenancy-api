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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Multi-tenant platform tenants.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the TenantService layer.
 */
class TenantController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TenantService $service
     * @param PlanEntitlementService $entitlements
     */
    public function __construct(
        private readonly TenantService $service,
        private readonly PlanEntitlementService $entitlements,
    ) {}

    /**
     * Get paginated tenant records.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $status = $request->query('status');

        $items = $this->service->getPaginated($perPage, $search, $status);

        return $this->paginated($items, TenantResource::collection($items), 'Tenants retrieved successfully.');
    }

    /**
     * List active tenants as value/label pairs for select inputs.
     *
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Tenant options retrieved successfully.');
    }

    /**
     * KPI card metrics for tenants.
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Tenant KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new tenant.
     *
     * @param StoreTenantRequest $request Validated request payload.
     *
     * @return JsonResponse
     */
    public function store(StoreTenantRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new TenantResource($item), 'Tenant created successfully.');
    }

    /**
     * Find tenant by route binding.
     *
     * @param Tenant $tenant Tenant instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(Tenant $tenant): JsonResponse
    {
        $item = $this->service->findOrFail($tenant->id);

        return $this->success(new TenantResource($item), 'Tenant retrieved successfully.');
    }

    /**
     * Update tenant.
     *
     * @param UpdateTenantRequest $request Validated request payload.
     * @param Tenant $tenant Tenant instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse
    {
        $item = $this->service->update($tenant, $request->validated());

        return $this->updated(new TenantResource($item), 'Tenant updated successfully.');
    }

    /**
     * Delete tenant.
     *
     * @param Tenant $tenant Tenant instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        $this->service->delete($tenant);

        return $this->deleted('Tenant deleted successfully.');
    }

    /**
     * Delete multiple tenants in one request.
     *
     * @param BulkDeleteTenantsRequest $request
     *
     * @return JsonResponse
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
     * @param Tenant $tenant Tenant instance resolved via route model binding.
     *
     * @return JsonResponse
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
     *
     * @return JsonResponse
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
     *
     * @return JsonResponse
     */
    public function getExpiring(int $days): JsonResponse
    {
        $items = $this->service->getExpiring($days);

        return $this->success(TenantResource::collection($items), 'Expiring tenants retrieved successfully.');
    }

    /**
     * Restore a soft-deleted tenant.
     *
     * @param string $id Trashed record identifier.
     *
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        $item = $this->service->restore($id);

        return $this->success(new TenantResource($item), 'Tenant restored successfully.');
    }

    /**
     * Restore multiple soft-deleted tenants.
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
            "{$restored} tenant(s) restored successfully.",
        );
    }

    /**
     * Permanently delete a tenant and its data.
     *
     * @param string $id Trashed record identifier.
     *
     * @return JsonResponse
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $this->service->forceDelete($id);

        return $this->deleted('Tenant permanently deleted.');
    }
}
