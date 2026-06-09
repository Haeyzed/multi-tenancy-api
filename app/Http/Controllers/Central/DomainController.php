<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreDomainRequest;
use App\Http\Requests\Central\UpdateDomainRequest;
use App\Http\Resources\Central\DomainResource;
use App\Models\Central\Domain;
use App\Services\Central\DomainService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant custom domains.
 */
class DomainController extends Controller
{
    public function __construct(
        private readonly DomainService $service,
    ) {}

    /**
     * Get paginated Domain records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $verified = QueryFilter::parseList($request->query('verified'));

        $items = $this->service->getPaginated($perPage, $search, $verified);

        return $this->paginated($items, DomainResource::collection($items), 'Domains retrieved successfully.');
    }

    /**
     * Create a new Domain.
     *
     * @param  StoreDomainRequest  $request  Validated request payload.
     */
    public function store(StoreDomainRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new DomainResource($item), 'Domain created successfully.');
    }

    /**
     * Find Domain by route binding.
     *
     * @param  Domain  $domain  Domain instance.
     */
    public function show(Domain $domain): JsonResponse
    {
        return $this->success(new DomainResource($domain), 'Domain retrieved successfully.');
    }

    /**
     * Update Domain.
     *
     * @param  UpdateDomainRequest  $request  Validated request payload.
     * @param  Domain  $domain  Domain instance.
     */
    public function update(UpdateDomainRequest $request, Domain $domain): JsonResponse
    {
        $item = $this->service->update($domain, $request->validated());

        return $this->updated(new DomainResource($item), 'Domain updated successfully.');
    }

    /**
     * Delete Domain.
     *
     * @param  Domain  $domain  Domain instance.
     */
    public function destroy(Domain $domain): JsonResponse
    {
        $this->service->delete($domain);

        return $this->deleted('Domain deleted successfully.');
    }

    /**
     * Set the domain as the tenant's primary domain.
     *
     * @param  Domain  $domain  Domain instance.
     */
    public function setPrimary(Domain $domain): JsonResponse
    {
        $item = $this->service->setPrimary($domain);

        return $this->success(new DomainResource($item), 'Domain set as primary.');
    }

    /**
     * Mark the domain as verified.
     *
     * @param  Domain  $domain  Domain instance.
     */
    public function verify(Domain $domain): JsonResponse
    {
        $item = $this->service->verify($domain);

        return $this->success(new DomainResource($item), 'Domain verified.');
    }
}
