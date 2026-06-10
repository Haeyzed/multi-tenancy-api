<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePlatformChangelogRequest;
use App\Http\Requests\Central\UpdatePlatformChangelogRequest;
use App\Http\Resources\Central\PlatformChangelogResource;
use App\Models\Central\PlatformChangelog;
use App\Services\Central\PlatformChangelogService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Platform changelog entries.
 */
class PlatformChangelogController extends Controller
{
    public function __construct(
        private readonly PlatformChangelogService $service,
    ) {}

    /**
     * Get paginated PlatformChangelog records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $type = QueryFilter::parseList($request->query('type'));
        $isPublished = QueryFilter::parseList($request->query('is_published'));

        $items = $this->service->getPaginated($perPage, $search, $type, $isPublished);

        return $this->paginated($items, PlatformChangelogResource::collection($items), 'Changelog entries retrieved successfully.');
    }

    /**
     * Create a new PlatformChangelog.
     *
     * @param  StorePlatformChangelogRequest  $request  Validated request payload.
     */
    public function store(StorePlatformChangelogRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PlatformChangelogResource($item), 'Changelog entry created successfully.');
    }

    /**
     * Find PlatformChangelog by route binding.
     *
     * @param  PlatformChangelog  $changelog  PlatformChangelog instance.
     */
    public function show(PlatformChangelog $changelog): JsonResponse
    {
        return $this->success(new PlatformChangelogResource($changelog), 'Changelog entry retrieved successfully.');
    }

    /**
     * Update PlatformChangelog.
     *
     * @param  UpdatePlatformChangelogRequest  $request  Validated request payload.
     * @param  PlatformChangelog  $changelog  PlatformChangelog instance.
     */
    public function update(UpdatePlatformChangelogRequest $request, PlatformChangelog $changelog): JsonResponse
    {
        $item = $this->service->update($changelog, $request->validated());

        return $this->updated(new PlatformChangelogResource($item), 'Changelog entry updated successfully.');
    }

    /**
     * Delete PlatformChangelog.
     *
     * @param  PlatformChangelog  $changelog  PlatformChangelog instance.
     */
    public function destroy(PlatformChangelog $changelog): JsonResponse
    {
        $this->service->delete($changelog);

        return $this->deleted('Changelog entry deleted successfully.');
    }
}
