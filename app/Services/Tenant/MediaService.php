<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Media;
use App\Models\Tenant\MediaLibraryFolder;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Tenant media library files and queries.
 */
class MediaService
{
    use DeletesManyRecords;

    private const LIBRARY_COLLECTION = 'library';

    private const LIBRARY_MODEL_TYPE = MediaLibraryFolder::class;

    /**
     * Base query for library media items.
     *
     * @return Builder<Media>
     */
    private function query(): Builder
    {
        return Media::query()
            ->where('collection_name', self::LIBRARY_COLLECTION)
            ->with(['folder', 'uploader']);
    }

    /**
     * Get paginated media library items.
     *
     * @return LengthAwarePaginator<int, Media>
     */
    public function getPaginated(
        int $perPage = 24,
        ?string $search = null,
        ?int $folderId = null,
        ?string $mimeType = null,
        bool $rootOnly = false,
    ): LengthAwarePaginator {
        return $this->query()
            ->search($search)
            ->when($folderId !== null, fn (Builder $q) => $q->where('folder_id', $folderId))
            ->when($folderId === null && $rootOnly, fn (Builder $q) => $q->whereNull('folder_id'))
            ->when($mimeType, fn (Builder $q) => $q->where('mime_type', 'like', "{$mimeType}%"))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Find media by ID or fail.
     */
    public function findOrFail(int $id): Media
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * Upload a file into the media library.
     *
     * @param  array<string, mixed>  $meta
     */
    public function upload(UploadedFile $file, array $meta = []): Media
    {
        $disk = (string) config('media-library.disk_name', 'public');
        $folderId = $meta['folder_id'] ?? null;
        $storedName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $directory = 'media/library/'.($folderId ?: 'root');
        $path = $file->storeAs($directory, $storedName, $disk);

        return Media::query()->create([
            'folder_id' => $folderId,
            'model_type' => self::LIBRARY_MODEL_TYPE,
            'model_id' => $folderId ?? 0,
            'uuid' => (string) Str::uuid(),
            'collection_name' => self::LIBRARY_COLLECTION,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'title' => $meta['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'alt_text' => $meta['alt_text'] ?? null,
            'uploaded_by' => Auth::id(),
            'file_name' => basename($path),
            'mime_type' => $file->getClientMimeType(),
            'disk' => $disk,
            'conversions_disk' => config('media-library.conversions_disk_name'),
            'size' => $file->getSize(),
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ]);
    }

    /**
     * Update media metadata.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Media $media, array $data): Media
    {
        $media->update($data);

        return $media->fresh(['folder', 'uploader']);
    }

    /**
     * Delete media and remove the underlying file from storage.
     */
    public function delete(Media $media): bool
    {
        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        if ($path && $disk->exists($path)) {
            $disk->delete($path);
        }

        return $media->delete();
    }

    /**
     * Delete multiple media items by ID.
     *
     * @param  list<int>  $ids
     */
    public function deleteMany(array $ids): int
    {
        $deleted = 0;

        Media::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Media $media) use (&$deleted): void {
                if ($this->delete($media)) {
                    $deleted++;
                }
            });

        return $deleted;
    }

    /**
     * KPI card metrics for the media library.
     *
     * @return list<array{key: string, label: string, value: int|string}>
     */
    public function getMetrics(): array
    {
        $total = Media::query()->where('collection_name', self::LIBRARY_COLLECTION)->count();
        $images = Media::query()
            ->where('collection_name', self::LIBRARY_COLLECTION)
            ->where('mime_type', 'like', 'image/%')
            ->count();
        $totalSize = (int) Media::query()
            ->where('collection_name', self::LIBRARY_COLLECTION)
            ->sum('size');

        return [
            ['key' => 'total', 'label' => 'Total Files', 'value' => $total],
            ['key' => 'images', 'label' => 'Images', 'value' => $images],
            ['key' => 'total_size_mb', 'label' => 'Storage (MB)', 'value' => round($totalSize / 1024 / 1024, 2)],
        ];
    }
}
