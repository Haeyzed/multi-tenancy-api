<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\ActivityResource;
use App\Models\Central\Activity;
use App\Services\Central\ActivityService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Central activity log entries.
 */
class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $service,
    )
    {
    }

    /**
     * Get paginated Activity records.
     *
     * @param Request $request Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $logName = QueryFilter::parseList($request->query('log_name'));
        $event = QueryFilter::parseList($request->query('event'));

        $items = $this->service->getPaginated($perPage, $search, $logName, $event);

        return $this->paginated($items, ActivityResource::collection($items), 'Activities retrieved successfully.');
    }

    /**
     * Create a new Activity.
     *
     * @param Request $request Validated request payload.
     */
    public function store(Request $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new ActivityResource($item), 'Activity created successfully.');
    }

    /**
     * Find Activity by route binding.
     *
     * @param Activity $activity Activity instance.
     */
    public function show(Activity $activity): JsonResponse
    {
        $item = $this->service->findOrFail($activity->id);

        return $this->success(new ActivityResource($item), 'Activity retrieved successfully.');
    }

    /**
     * Update Activity.
     *
     * @param Request $request Validated request payload.
     * @param Activity $activity Activity instance.
     */
    public function update(Request $request, Activity $activity): JsonResponse
    {
        $item = $this->service->update($activity, $request->validated());

        return $this->updated(new ActivityResource($item), 'Activity updated successfully.');
    }

    /**
     * Delete Activity.
     *
     * @param Activity $activity Activity instance.
     */
    public function destroy(Activity $activity): JsonResponse
    {
        $this->service->delete($activity);

        return $this->deleted('Activity deleted successfully.');
    }
}
