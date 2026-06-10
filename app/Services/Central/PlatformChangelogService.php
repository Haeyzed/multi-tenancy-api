<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\PlatformChangelog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central PlatformChangelog records and queries.
 */
class PlatformChangelogService
{
    /**
     * Get all PlatformChangelog records.
     *
     * @return Collection<int, PlatformChangelog>
     */
    public function getAll(): Collection
    {
        return PlatformChangelog::query()->latest('published_at')->get();
    }

    /**
     * @param  list<string>  $type
     * @param  list<string>  $isPublished
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $type = [],
        array $isPublished = [],
    ): LengthAwarePaginator {
        return PlatformChangelog::query()
            ->search($search)
            ->filterType($type)
            ->filterIsPublished($isPublished)
            ->latest('published_at')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find PlatformChangelog by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?PlatformChangelog
    {
        return PlatformChangelog::query()->find($id);
    }

    /**
     * Find PlatformChangelog by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): PlatformChangelog
    {
        return PlatformChangelog::query()->findOrFail($id);
    }

    /**
     * Create a new PlatformChangelog.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PlatformChangelog
    {
        return PlatformChangelog::query()->create($data);
    }

    /**
     * Update PlatformChangelog.
     *
     * @param  PlatformChangelog  $platformChangelog  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(PlatformChangelog $platformChangelog, array $data): PlatformChangelog
    {
        $platformChangelog->update($data);

        return $platformChangelog->fresh();
    }

    /**
     * Delete PlatformChangelog.
     *
     * @param  PlatformChangelog  $platformChangelog  The model instance to delete.
     */
    public function delete(PlatformChangelog $platformChangelog): bool
    {
        return (bool) $platformChangelog->delete();
    }

    /**
     * Filter by type.
     *
     * @param  string  $type  Content type to filter by.
     * @return Collection<int, PlatformChangelog>
     */
    public function getByType(string $type): Collection
    {
        return PlatformChangelog::query()->where('type', $type)->get();
    }

    /**
     * Get published changelog entries.
     *
     * @return Collection<int, PlatformChangelog>
     */
    public function getPublished(): Collection
    {
        return PlatformChangelog::query()->where('is_published', true)
            ->orderBy('published_at', 'asc')
            ->get();
    }
}
