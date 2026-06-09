<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Plan;
use App\Models\Central\PlatformAnnouncement;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central PlatformAnnouncement records and queries.
 */
class PlatformAnnouncementService
{
    use DeletesManyRecords;
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
     * @param  string|null  $search  Optional search term.
     * @param  list<string>  $isActive
     * @param  list<string>  $types
     * @param  list<string>  $targetAudiences
     * @return LengthAwarePaginator<int, PlatformAnnouncement>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $isActive = [],
        array $types = [],
        array $targetAudiences = [],
    ): LengthAwarePaginator {
        $paginator = PlatformAnnouncement::query()
            ->search($search)
            ->filterIsActive($isActive)
            ->filterType($types)
            ->filterTargetAudience($targetAudiences)
            ->latest()
            ->paginate($perPage);

        $this->hydrateTargetPlanNames($paginator->getCollection());

        return $paginator;
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
        $announcement = PlatformAnnouncement::query()->findOrFail($id);
        $this->hydrateTargetPlanNames(collect([$announcement]));

        return $announcement;
    }

    /**
     * Create a new PlatformAnnouncement.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PlatformAnnouncement
    {
        $announcement = PlatformAnnouncement::query()->create($data);
        $this->hydrateTargetPlanNames(collect([$announcement]));

        return $announcement;
    }

    /**
     * Update PlatformAnnouncement.
     *
     * @param  PlatformAnnouncement  $platformAnnouncement  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(PlatformAnnouncement $platformAnnouncement, array $data): PlatformAnnouncement
    {
        $platformAnnouncement->update($data);

        $announcement = $platformAnnouncement->fresh();
        $this->hydrateTargetPlanNames(collect([$announcement]));

        return $announcement;
    }

    /**
     * Delete PlatformAnnouncement.
     *
     * @param  PlatformAnnouncement  $platformAnnouncement  The model instance to delete.
     */
    public function delete(PlatformAnnouncement $platformAnnouncement): bool
    {
        return (bool) $platformAnnouncement->delete();
    }

    /**
     * Delete multiple announcements by ID.
     *
     * @param  list<int>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->deleteManyByIds(PlatformAnnouncement::class, $ids);
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

    /**
     * KPI card metrics for announcements.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    /**
     * Resolve plan names for API responses without N+1 queries.
     *
     * @param  Collection<int, PlatformAnnouncement>  $announcements
     */
    private function hydrateTargetPlanNames(Collection $announcements): void
    {
        if ($announcements->isEmpty()) {
            return;
        }

        $planIds = $announcements
            ->flatMap(fn (PlatformAnnouncement $announcement) => $announcement->target_plans ?? [])
            ->unique()
            ->filter()
            ->values()
            ->all();

        if ($planIds === []) {
            return;
        }

        $namesById = Plan::query()
            ->whereIn('id', $planIds)
            ->pluck('name', 'id');

        foreach ($announcements as $announcement) {
            $announcement->setAttribute(
                'target_plan_names',
                collect($announcement->target_plans ?? [])
                    ->map(fn (string $id): ?string => $namesById[$id] ?? null)
                    ->filter()
                    ->values()
                    ->all(),
            );
        }
    }

    public function getMetrics(): array
    {
        $total = PlatformAnnouncement::query()->count();
        $active = PlatformAnnouncement::query()->where('is_active', true)->count();
        $live = PlatformAnnouncement::query()->currentlyLive()->count();
        $alerts = PlatformAnnouncement::query()->where('type', 'alert')->count();

        return [
            ['key' => 'total', 'label' => 'Total Announcements', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'live', 'label' => 'Currently Live', 'value' => $live],
            ['key' => 'alerts', 'label' => 'Alerts', 'value' => $alerts],
        ];
    }
}
