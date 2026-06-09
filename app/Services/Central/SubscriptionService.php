<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\SubscriptionStatus;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Subscription records and queries.
 */
class SubscriptionService
{
    use DeletesManyRecords;
    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'tenant',
        'plan',
        'latestInvoice',
        'invoices',
        'subscriptionItems',
        'usageRecords',
    ];

    public function __construct(
        private readonly SubscriptionLifecycleService $lifecycle,
    ) {}

    /**
     * Base query with subscription detail relations.
     *
     * @return Builder<Subscription>
     */
    private function queryWithDetails(): Builder
    {
        return Subscription::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Get all Subscription records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, Subscription>
     */
    public function getAll(?string $search = null): Collection
    {
        return $this->queryWithDetails()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated Subscription records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, Subscription>
     */
    /**
     * @param  list<string>  $status
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $status = [],
    ): LengthAwarePaginator {
        return $this->queryWithDetails()
            ->forTenant()
            ->search($search)
            ->filterStatus($status)
            ->paginate($perPage);
    }

    /**
     * Find Subscription by ID.
     *
     * @param  string  $id  Record identifier.
     */
    public function find(string $id): ?Subscription
    {
        return $this->queryWithDetails()->find($id);
    }

    /**
     * Find Subscription by ID or fail.
     *
     * @param  string  $id  Record identifier.
     */
    public function findOrFail(string $id): Subscription
    {
        return $this->queryWithDetails()->findOrFail($id);
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
     * Delete multiple subscriptions by ID.
     *
     * @param  list<string>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->deleteManyByIds(Subscription::class, $ids);
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

    /**
     * KPI card metrics for subscriptions.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $counts = Subscription::query()
            ->forTenant()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            ['key' => 'total', 'label' => 'Total Subscriptions', 'value' => (int) $counts->sum()],
            ['key' => 'active', 'label' => 'Active', 'value' => (int) ($counts[SubscriptionStatus::Active->value] ?? 0)],
            ['key' => 'trialing', 'label' => 'Trialing', 'value' => (int) ($counts[SubscriptionStatus::Trialing->value] ?? 0)],
            ['key' => 'past_due', 'label' => 'Past Due', 'value' => (int) ($counts[SubscriptionStatus::PastDue->value] ?? 0)],
            ['key' => 'cancelled', 'label' => 'Cancelled', 'value' => (int) ($counts[SubscriptionStatus::Cancelled->value] ?? 0)],
            ['key' => 'paused', 'label' => 'Paused', 'value' => (int) ($counts[SubscriptionStatus::Paused->value] ?? 0)],
            ['key' => 'expired', 'label' => 'Expired', 'value' => (int) ($counts[SubscriptionStatus::Expired->value] ?? 0)],
        ];
    }
}
