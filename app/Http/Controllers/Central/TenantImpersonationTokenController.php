<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantImpersonationTokenRequest;
use App\Http\Requests\Central\UpdateTenantImpersonationTokenRequest;
use App\Http\Resources\Central\TenantImpersonationTokenResource;
use App\Models\Central\TenantImpersonationToken;
use App\Services\Central\TenantImpersonationTokenService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Short-lived tokens for tenant impersonation.
 */
class TenantImpersonationTokenController extends Controller
{
    public function __construct(
        private readonly TenantImpersonationTokenService $service,
    ) {}

    /**
     * Get paginated TenantImpersonationToken records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $status = QueryFilter::parseList($request->query('status'));

        $items = $this->service->getPaginated($perPage, $search, $status);

        return $this->paginated($items, TenantImpersonationTokenResource::collection($items), 'Impersonation tokens retrieved successfully.');
    }

    /**
     * Create a new TenantImpersonationToken.
     *
     * @param  StoreTenantImpersonationTokenRequest  $request  Validated request payload.
     */
    public function store(StoreTenantImpersonationTokenRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new TenantImpersonationTokenResource($item), 'Impersonation token created successfully.');
    }

    /**
     * Find TenantImpersonationToken by route binding.
     *
     * @param  TenantImpersonationToken  $impersonationToken  TenantImpersonationToken instance.
     */
    public function show(TenantImpersonationToken $impersonationToken): JsonResponse
    {
        return $this->success(
            new TenantImpersonationTokenResource(
                $impersonationToken->load(['tenant', 'administrator']),
            ),
            'Impersonation token retrieved successfully.',
        );
    }

    /**
     * Update TenantImpersonationToken.
     *
     * @param  UpdateTenantImpersonationTokenRequest  $request  Validated request payload.
     * @param  TenantImpersonationToken  $impersonationToken  TenantImpersonationToken instance.
     */
    public function update(UpdateTenantImpersonationTokenRequest $request, TenantImpersonationToken $impersonationToken): JsonResponse
    {
        $item = $this->service->update($impersonationToken, $request->validated());

        return $this->updated(new TenantImpersonationTokenResource($item), 'Impersonation token updated successfully.');
    }

    /**
     * Delete TenantImpersonationToken.
     *
     * @param  TenantImpersonationToken  $impersonationToken  TenantImpersonationToken instance.
     */
    public function destroy(TenantImpersonationToken $impersonationToken): JsonResponse
    {
        $this->service->delete($impersonationToken);

        return $this->deleted('Impersonation token deleted successfully.');
    }

    /**
     * Mark an impersonation token as used.
     *
     * @param  TenantImpersonationToken  $impersonationToken  TenantImpersonationToken instance.
     */
    public function markAsUsed(TenantImpersonationToken $impersonationToken): JsonResponse
    {
        $item = $this->service->markAsUsed($impersonationToken);

        return $this->success(new TenantImpersonationTokenResource($item), 'Impersonation token marked as used.');
    }
}
