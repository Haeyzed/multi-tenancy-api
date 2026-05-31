<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\SubscriptionEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central SubscriptionEvent records and queries.
 */
class SubscriptionEventService
{
    /**
     * Get all SubscriptionEvent records.
     *
     * @return Collection<int, SubscriptionEvent>
     */
    public function getAll(): Collection
    {
        return SubscriptionEvent::query()->get();
    }

    /**
     * Get paginated SubscriptionEvent records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, SubscriptionEvent>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return SubscriptionEvent::query()->paginate($perPage);
    }

    /**
     * Find SubscriptionEvent by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?SubscriptionEvent
    {
        return SubscriptionEvent::query()->find($id);
    }

    /**
     * Find SubscriptionEvent by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): SubscriptionEvent
    {
        return SubscriptionEvent::query()->findOrFail($id);
    }

    /**
     * Create a new SubscriptionEvent.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SubscriptionEvent
    {
        return SubscriptionEvent::query()->create($data);
    }

    /**
     * Update SubscriptionEvent.
     *
     * @param  SubscriptionEvent  $subscriptionEvent  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(SubscriptionEvent $subscriptionEvent, array $data): SubscriptionEvent
    {
        $subscriptionEvent->update($data);

        return $subscriptionEvent->fresh();
    }

    /**
     * Delete SubscriptionEvent.
     *
     * @param  SubscriptionEvent  $subscriptionEvent  The model instance to delete.
     */
    public function delete(SubscriptionEvent $subscriptionEvent): bool
    {
        return $subscriptionEvent->delete();
    }

    /**
     * Filter by subscription.
     *
     * @param  string  $subscriptionId  Subscription UUID to filter by.
     * @return Collection<int, SubscriptionEvent>
     */
    public function getBySubscription(string $subscriptionId): Collection
    {
        return SubscriptionEvent::query()->where('subscription_id', $subscriptionId)->get();
    }

    /**
     * Filter by event type.
     *
     * @param  string  $eventType  Subscription event type to filter by.
     * @return Collection<int, SubscriptionEvent>
     */
    public function getByEventType(string $eventType): Collection
    {
        return SubscriptionEvent::query()->where('event_type', $eventType)->get();
    }
}
