<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\UsageRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central UsageRecord records and queries.
 */
class UsageRecordService
{
    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'tenant',
        'subscription',
    ];

    /**
     * Get all UsageRecord records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, UsageRecord>
     */
    public function getAll(?string $search = null): Collection
    {
        return UsageRecord::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated UsageRecord records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, UsageRecord>
     */
    /**
     * @param  list<string>  $metrics
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $metrics = [],
    ): LengthAwarePaginator {
        return UsageRecord::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterMetric($metrics)
            ->latest('recorded_at')
            ->paginate($perPage);
    }

    /**
     * Find UsageRecord by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?UsageRecord
    {
        return UsageRecord::query()->find($id);
    }

    /**
     * Find UsageRecord by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): UsageRecord
    {
        return UsageRecord::query()->findOrFail($id);
    }

    /**
     * Create a new UsageRecord.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): UsageRecord
    {
        return UsageRecord::query()
            ->create($data)
            ->load(self::LIST_RELATIONS);
    }

    /**
     * Update UsageRecord.
     *
     * @param  UsageRecord  $usageRecord  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(UsageRecord $usageRecord, array $data): UsageRecord
    {
        $usageRecord->update($data);

        return $usageRecord->fresh(self::LIST_RELATIONS);
    }

    /**
     * Delete UsageRecord.
     *
     * @param  UsageRecord  $usageRecord  The model instance to delete.
     */
    public function delete(UsageRecord $usageRecord): bool
    {
        return $usageRecord->delete();
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, UsageRecord>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return UsageRecord::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by subscription.
     *
     * @param  string  $subscriptionId  Subscription UUID to filter by.
     * @return Collection<int, UsageRecord>
     */
    public function getBySubscription(string $subscriptionId): Collection
    {
        return UsageRecord::query()->where('subscription_id', $subscriptionId)->get();
    }

    /**
     * Filter by metric.
     *
     * @param  string  $metric  Usage metric name to filter by.
     * @return Collection<int, UsageRecord>
     */
    public function getByMetric(string $metric): Collection
    {
        return UsageRecord::query()->where('metric', $metric)->get();
    }
}
