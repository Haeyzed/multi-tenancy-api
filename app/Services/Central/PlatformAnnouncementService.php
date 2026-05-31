<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\PlatformAnnouncement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central PlatformAnnouncement records and queries.
 */
class PlatformAnnouncementService
{
    /**
     * Get all PlatformAnnouncement records.
     *
     * @return Collection<int, PlatformAnnouncement>
     */
    public function getAll(): Collection
    {
        return PlatformAnnouncement::query()->get();
    }

    /**
     * Get paginated PlatformAnnouncement records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, PlatformAnnouncement>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return PlatformAnnouncement::query()->paginate($perPage);
    }

    /**
     * Find PlatformAnnouncement by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?PlatformAnnouncement
    {
        return PlatformAnnouncement::query()->find($id);
    }

    /**
     * Find PlatformAnnouncement by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): PlatformAnnouncement
    {
        return PlatformAnnouncement::query()->findOrFail($id);
    }

    /**
     * Create a new PlatformAnnouncement.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PlatformAnnouncement
    {
        return PlatformAnnouncement::query()->create($data);
    }

    /**
     * Update PlatformAnnouncement.
     *
     * @param  PlatformAnnouncement  $platformAnnouncement  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(PlatformAnnouncement $platformAnnouncement, array $data): PlatformAnnouncement
    {
        $platformAnnouncement->query()->update($data);

        return $platformAnnouncement->fresh();
    }

    /**
     * Delete PlatformAnnouncement.
     *
     * @param  PlatformAnnouncement  $platformAnnouncement  The model instance to delete.
     */
    public function delete(PlatformAnnouncement $platformAnnouncement): bool
    {
        return $platformAnnouncement->query()->delete() > 0;
    }

    /**
     * Get only active records.
     *
     * @return Collection<int, PlatformAnnouncement>
     */
    public function getActive(): Collection
    {
        return PlatformAnnouncement::query()->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();
    }

    /**
     * Filter by type.
     *
     * @param  string  $type  Content type to filter by.
     * @return Collection<int, PlatformAnnouncement>
     */
    public function getByType(string $type): Collection
    {
        return PlatformAnnouncement::query()->where('type', $type)->get();
    }
}
