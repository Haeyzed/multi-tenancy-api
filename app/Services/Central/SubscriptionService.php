<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\SubscriptionStatus;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Subscription records and queries.
 */
class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionLifecycleService $lifecycle,
    ) {}

    /**
     * Get all Subscription records.
     *
     * @return Collection<int, Subscription>
     */
    public function getAll(): Collection
    {
        return Subscription::query()->get();
    }

    /**
     * Get paginated Subscription records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, Subscription>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Subscription::query()->paginate($perPage);
    }

    /**
     * Find Subscription by ID.
     *
     * @param  string  $id  Record identifier.
     */
    public function find(string $id): ?Subscription
    {
        return Subscription::query()->find($id);
    }

    /**
     * Find Subscription by ID or fail.
     *
     * @param  string  $id  Record identifier.
     */
    public function findOrFail(string $id): Subscription
    {
        return Subscription::query()->findOrFail($id);
    }

    /**
     * Create a new Subscription via lifecycle (tenant + plan required in data).
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Subscription
    {
        $tenant = Tenant::query()->findOrFail($data['tenant_id']);
        $plan = Plan::query()->findOrFail($data['plan_id']);

        return $this->lifecycle->subscribe(
            $tenant,
            $plan,
            BillingCycle::from($data['billing_cycle']),
            EventTriggeredBy::Admin,
        );
    }

    /**
     * Update Subscription.
     *
     * @param  Subscription  $subscription  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription->fresh();
    }

    /**
     * Delete Subscription.
     *
     * @param  Subscription  $subscription  The model instance to delete.
     */
    public function delete(Subscription $subscription): bool
    {
        return Subscription::query()
            ->whereKey($subscription->getKey())
            ->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, Subscription>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return Subscription::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param  string  $status  Status value to filter by.
     * @return Collection<int, Subscription>
     */
    public function getByStatus(string $status): Collection
    {
        return Subscription::query()->where('status', $status)->get();
    }

    /**
     * Filter by plan.
     *
     * @param  string  $planId  Plan UUID to filter by.
     * @return Collection<int, Subscription>
     */
    public function getByPlan(string $planId): Collection
    {
        return Subscription::query()->where('plan_id', $planId)->get();
    }

    /**
     * Cancel a subscription.
     *
     * @param  string|null  $reason  Optional cancellation reason.
     */
    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        return $this->lifecycle->cancel($subscription, $reason, EventTriggeredBy::Admin);
    }

    /**
     * Renew a subscription billing period.
     */
    public function renew(Subscription $subscription): Subscription
    {
        return $this->lifecycle->renew($subscription, EventTriggeredBy::Admin);
    }

    /**
     * Upgrade subscription to a new plan.
     */
    public function upgrade(Subscription $subscription, Plan $plan): Subscription
    {
        return $this->lifecycle->upgrade($subscription, $plan, EventTriggeredBy::Admin);
    }

    /**
     * Downgrade subscription to a new plan.
     */
    public function downgrade(Subscription $subscription, Plan $plan): Subscription
    {
        return $this->lifecycle->downgrade($subscription, $plan, EventTriggeredBy::Admin);
    }

    /**
     * Reactivate a cancelled subscription.
     */
    public function reactivate(Subscription $subscription): Subscription
    {
        return $this->lifecycle->reactivate($subscription, EventTriggeredBy::Admin);
    }

    /**
     * Get active subscriptions with tenant and plan.
     *
     * @return Collection<int, Subscription>
     */
    public function getActiveWithDetails(): Collection
    {
        return Subscription::query()->with(['tenant', 'plan'])
            ->whereIn('status', [
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Trialing->value,
            ])
            ->get();
    }
}
