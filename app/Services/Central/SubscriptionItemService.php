<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\SubscriptionItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central SubscriptionItem records and queries.
 */
class SubscriptionItemService
{
    /**
     * Get all SubscriptionItem records.
     *
     * @return Collection<int, SubscriptionItem>
     */
    public function getAll(): Collection
    {
        return SubscriptionItem::query()->get();
    }

    /**
     * Get paginated SubscriptionItem records.
     *
     * @param int $perPage Number of records per page.
     * @return LengthAwarePaginator<int, SubscriptionItem>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return SubscriptionItem::query()->paginate($perPage);
    }

    /**
     * Find SubscriptionItem by ID.
     *
     * @param int $id Record identifier.
     */
    public function find(int $id): ?SubscriptionItem
    {
        return SubscriptionItem::query()->find($id);
    }

    /**
     * Find SubscriptionItem by ID or fail.
     *
     * @param int $id Record identifier.
     */
    public function findOrFail(int $id): SubscriptionItem
    {
        return SubscriptionItem::query()->findOrFail($id);
    }

    /**
     * Create a new SubscriptionItem.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): SubscriptionItem
    {
        return SubscriptionItem::query()->create($data);
    }

    /**
     * Update SubscriptionItem.
     *
     * @param SubscriptionItem $subscriptionItem The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(SubscriptionItem $subscriptionItem, array $data): SubscriptionItem
    {
        $subscriptionItem->query()->update($data);

        return $subscriptionItem->fresh();
    }

    /**
     * Delete SubscriptionItem.
     *
     * @param SubscriptionItem $subscriptionItem The model instance to delete.
     */
    public function delete(SubscriptionItem $subscriptionItem): bool
    {
        return $subscriptionItem->query()->delete() > 0;
    }

    /**
     * Filter by plan.
     *
     * @param string $planId Plan UUID to filter by.
     * @return Collection<int, SubscriptionItem>
     */
    public function getByPlan(string $planId): Collection
    {
        return SubscriptionItem::query()->where('plan_id', $planId)->get();
    }

    /**
     * Filter by subscription.
     *
     * @param string $subscriptionId Subscription UUID to filter by.
     * @return Collection<int, SubscriptionItem>
     */
    public function getBySubscription(string $subscriptionId): Collection
    {
        return SubscriptionItem::query()->where('subscription_id', $subscriptionId)->get();
    }
}
