<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Plan;
use App\Models\Central\PlatformAnnouncement;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Central platform announcement records and queries.
 *
 * Encapsulates all business logic for announcement management, including
 * creation, updates, pagination, filtering, deletion, and KPI metrics.
 */
class PlatformAnnouncementService
{
    /**
     * Get paginated announcement records.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param mixed $isActive Active/inactive filter tokens.
     * @param mixed $types Announcement type filter tokens.
     * @param mixed $targetAudiences Target audience filter tokens.
     *
     * @return LengthAwarePaginator<int, PlatformAnnouncement>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        mixed $isActive = null,
        mixed $types = null,
        mixed $targetAudiences = null,
    ): LengthAwarePaginator {
        $paginator = PlatformAnnouncement::query()
            ->search($search)
            ->filterIsActive(QueryFilter::filterList($isActive))
            ->filterType(QueryFilter::filterList($types))
            ->filterTargetAudience(QueryFilter::filterList($targetAudiences))
            ->latest()
            ->paginate($perPage);

        $this->hydrateTargetPlanNames($paginator->getCollection());

        return $paginator;
    }

    /**
     * Find announcement by ID or fail.
     *
     * @param int $id Record identifier.
     *
     * @return PlatformAnnouncement
     */
    public function findOrFail(int $id): PlatformAnnouncement
    {
        $announcement = PlatformAnnouncement::query()->findOrFail($id);
        $this->hydrateTargetPlanNames(collect([$announcement]));

        return $announcement;
    }

    /**
     * Create a new platform announcement.
     *
     * @param array<string, mixed> $data
     *
     * @return PlatformAnnouncement
     */
    public function create(array $data): PlatformAnnouncement
    {
        $announcement = PlatformAnnouncement::query()->create($data);
        $this->hydrateTargetPlanNames(collect([$announcement]));

        return $announcement;
    }

    /**
     * Update platform announcement.
     *
     * @param PlatformAnnouncement $platformAnnouncement The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     *
     * @return PlatformAnnouncement
     */
    public function update(PlatformAnnouncement $platformAnnouncement, array $data): PlatformAnnouncement
    {
        $platformAnnouncement->update($data);

        $announcement = $platformAnnouncement->fresh();
        $this->hydrateTargetPlanNames(collect([$announcement]));

        return $announcement;
    }

    /**
     * Delete a single platform announcement.
     *
     * @param PlatformAnnouncement $platformAnnouncement The model instance to delete.
     *
     * @return bool
     */
    public function delete(PlatformAnnouncement $platformAnnouncement): bool
    {
        return $platformAnnouncement->delete();
    }

    /**
     * Delete multiple announcements by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of deleted records.
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = PlatformAnnouncement::query()->whereIn('id', $ids)->get();
            $deleted = 0;

            foreach ($records as $record) {
                if ($record->delete()) {
                    $deleted++;
                }
            }

            return $deleted;
        });
    }

    /**
     * Get only active announcements within their schedule window.
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
     * @param string $type Content type to filter by.
     *
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

    /**
     * Resolve plan names for API responses without N+1 queries.
     *
     * @param Collection<int, PlatformAnnouncement> $announcements
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
                    ->map(fn (int|string $id): ?string => $namesById[$id] ?? null)
                    ->filter()
                    ->values()
                    ->all(),
            );
        }
    }
}
