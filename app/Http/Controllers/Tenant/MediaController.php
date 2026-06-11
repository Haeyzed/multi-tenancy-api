<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteMediaRequest;
use App\Http\Requests\Tenant\UpdateMediaRequest;
use App\Http\Requests\Tenant\UploadMediaRequest;
use App\Http\Resources\Tenant\MediaResource;
use App\Models\Tenant\Media;
use App\Services\Tenant\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Media library files for the tenant store.
 */
class MediaController extends Controller
{
    public function __construct(
        private readonly MediaService $service,
    ) {}

    /**
     * Get paginated media library items.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 24);
        $search = $request->query('search');
        $folderId = $request->filled('folder_id') ? $request->integer('folder_id') : null;
        $mimeType = $request->query('mime_type');
        $rootOnly = $request->boolean('root_only');

        $items = $this->service->getPaginated(
            $perPage,
            is_string($search) ? $search : null,
            $folderId,
            is_string($mimeType) ? $mimeType : null,
            $rootOnly,
        );

        return $this->paginated($items, MediaResource::collection($items), 'Media retrieved successfully.');
    }

    /**
     * KPI card metrics for the media library.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Media KPI metrics retrieved successfully.',
        );
    }

    /**
     * Upload a file to the media library.
     *
     * @param  UploadMediaRequest  $request  Validated upload payload.
     */
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $item = $this->service->upload(
            $request->file('file'),
            $request->safe()->except(['file']),
        );

        return $this->created(new MediaResource($item), 'Media uploaded successfully.');
    }

    /**
     * Find media by route binding.
     *
     * @param  Media  $media  Media instance.
     */
    public function show(Media $media): JsonResponse
    {
        $item = $this->service->findOrFail($media->id);

        return $this->success(new MediaResource($item), 'Media retrieved successfully.');
    }

    /**
     * Update media metadata.
     *
     * @param  UpdateMediaRequest  $request  Validated request payload.
     * @param  Media  $media  Media instance.
     */
    public function update(UpdateMediaRequest $request, Media $media): JsonResponse
    {
        $item = $this->service->update($media, $request->validated());

        return $this->updated(new MediaResource($item), 'Media updated successfully.');
    }

    /**
     * Delete media.
     *
     * @param  Media  $media  Media instance.
     */
    public function destroy(Media $media): JsonResponse
    {
        $this->service->delete($media);

        return $this->deleted('Media deleted successfully.');
    }

    /**
     * Delete multiple media items in one request.
     */
    public function bulkDestroy(BulkDeleteMediaRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} media file(s) deleted successfully.",
        );
    }
}
