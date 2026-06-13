<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\BulkDeleteAnnouncementsRequest;
use App\Http\Requests\Central\StorePlatformAnnouncementRequest;
use App\Http\Requests\Central\UpdatePlatformAnnouncementRequest;
use App\Http\Resources\Central\PlatformAnnouncementResource;
use App\Models\Central\PlatformAnnouncement;
use App\Services\Central\PlatformAnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Platform-wide announcements for tenants.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the PlatformAnnouncementService layer.
 */
class PlatformAnnouncementController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PlatformAnnouncementService $service
     */
    public function __construct(
        private readonly PlatformAnnouncementService $service,
    ) {}

    /**
     * Get paginated announcement records.
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
        $types = $request->query('type');
        $targetAudiences = $request->query('target_audience');
        $items = $this->service->getPaginated($perPage, $search, $isActive, $types, $targetAudiences);

        return $this->paginated($items, PlatformAnnouncementResource::collection($items), 'Announcements retrieved successfully.');
    }

    /**
     * KPI card metrics for announcements.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Announcement KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new PlatformAnnouncement.
     *
     * @param StorePlatformAnnouncementRequest $request Validated request payload.
     */
    public function store(StorePlatformAnnouncementRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PlatformAnnouncementResource($item), 'Announcement created successfully.');
    }

    /**
     * Find announcement by route binding.
     *
     * @param PlatformAnnouncement $announcement Announcement instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(PlatformAnnouncement $announcement): JsonResponse
    {
        $item = $this->service->findOrFail($announcement->id);

        return $this->success(new PlatformAnnouncementResource($item), 'Announcement retrieved successfully.');
    }

    /**
     * Update PlatformAnnouncement.
     *
     * @param UpdatePlatformAnnouncementRequest $request Validated request payload.
     * @param PlatformAnnouncement $announcement PlatformAnnouncement instance.
     */
    public function update(UpdatePlatformAnnouncementRequest $request, PlatformAnnouncement $announcement): JsonResponse
    {
        $item = $this->service->update($announcement, $request->validated());

        return $this->updated(new PlatformAnnouncementResource($item), 'Announcement updated successfully.');
    }

    /**
     * Delete PlatformAnnouncement.
     *
     * @param PlatformAnnouncement $announcement PlatformAnnouncement instance.
     */
    public function destroy(PlatformAnnouncement $announcement): JsonResponse
    {
        $this->service->delete($announcement);

        return $this->deleted('Announcement deleted successfully.');
    }

    /**
     * Delete multiple announcements in one request.
     */
    public function bulkDestroy(BulkDeleteAnnouncementsRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} announcement(s) deleted successfully.",
        );
    }
}
