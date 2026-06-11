<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreApiKeyRequest;
use App\Http\Requests\Central\UpdateApiKeyRequest;
use App\Http\Resources\Central\ApiKeyResource;
use App\Models\Central\ApiKey;
use App\Services\Central\ApiKeyService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant API keys for programmatic access.
 */
class ApiKeyController extends Controller
{
    public function __construct(
        private readonly ApiKeyService $service,
    )
    {
    }

    /**
     * Get paginated ApiKey records.
     *
     * @param Request $request Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = QueryFilter::parseList($request->query('is_active'));

        $items = $this->service->getPaginated($perPage, $search, $isActive);

        return $this->paginated($items, ApiKeyResource::collection($items), 'API keys retrieved successfully.');
    }

    /**
     * KPI card metrics for API keys.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'API key KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new ApiKey.
     *
     * @param StoreApiKeyRequest $request Validated request payload.
     */
    public function store(StoreApiKeyRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new ApiKeyResource($item), 'Api key created successfully.');
    }

    /**
     * Find ApiKey by route binding.
     *
     * @param ApiKey $apiKey ApiKey instance.
     */
    public function show(ApiKey $apiKey): JsonResponse
    {
        return $this->success(new ApiKeyResource($apiKey), 'API key retrieved successfully.');
    }

    /**
     * Update ApiKey.
     *
     * @param UpdateApiKeyRequest $request Validated request payload.
     * @param ApiKey $apiKey ApiKey instance.
     */
    public function update(UpdateApiKeyRequest $request, ApiKey $apiKey): JsonResponse
    {
        $item = $this->service->update($apiKey, $request->validated());

        return $this->updated(new ApiKeyResource($item), 'Api key updated successfully.');
    }

    /**
     * Delete ApiKey.
     *
     * @param ApiKey $apiKey ApiKey instance.
     */
    public function destroy(ApiKey $apiKey): JsonResponse
    {
        $this->service->delete($apiKey);

        return $this->deleted('Api key deleted successfully.');
    }

    /**
     * Record API key usage by updating last_used_at.
     *
     * @param ApiKey $apiKey ApiKey instance.
     */
    public function recordUsage(ApiKey $apiKey): JsonResponse
    {
        $item = $this->service->recordUsage($apiKey);

        return $this->success(new ApiKeyResource($item), 'API key usage recorded.');
    }

    /**
     * Revoke an API key by deactivating it.
     *
     * @param ApiKey $apiKey ApiKey instance.
     */
    public function revoke(ApiKey $apiKey): JsonResponse
    {
        $item = $this->service->revoke($apiKey);

        return $this->success(new ApiKeyResource($item), 'API key revoked.');
    }
}
