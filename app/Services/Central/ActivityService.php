<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Activity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Activity records and queries.
 */
class ActivityService
{
    /**
     * Get all Activity records.
     *
     * @return Collection<int, Activity>
     */
    public function getAll(): Collection
    {
        return Activity::query()->get();
    }

    /**
     * Get paginated Activity records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, Activity>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Activity::query()->paginate($perPage);
    }

    /**
     * Find Activity by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?Activity
    {
        return Activity::query()->find($id);
    }

    /**
     * Find Activity by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): Activity
    {
        return Activity::query()->findOrFail($id);
    }

    /**
     * Create a new Activity.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Activity
    {
        return Activity::query()->create($data);
    }

    /**
     * Update Activity.
     *
     * @param  Activity  $activity  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Activity $activity, array $data): Activity
    {
        $activity->update($data);

        return $activity->fresh();
    }

    /**
     * Delete Activity.
     *
     * @param  Activity  $activity  The model instance to delete.
     */
    public function delete(Activity $activity): bool
    {
        return $activity->delete();
    }
}
