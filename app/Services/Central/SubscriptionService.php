<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\SubscriptionStatus;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Central tenant subscription records and queries.
 *
 * Encapsulates all business logic for subscription management, including
 * creation, updates, pagination, filtering, deletion, lifecycle actions,
 * and KPI metrics.
 */
class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionLifecycleService $lifecycle,
    ) {}

    /**
     * Get paginated subscription records with eager loaded relations.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param mixed $status Subscription status filter tokens.
     *
     * @return LengthAwarePaginator<int, Subscription>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        mixed $status = null,
    ): LengthAwarePaginator {
        return Subscription::query()
            ->with([
                'tenant',
                'plan',
                'latestInvoice',
                'invoices',
                'subscriptionItems',
                'usageRecords',
            ])
            ->forTenant()
            ->search($search)
            ->filterStatus(QueryFilter::filterList($status))
            ->paginate($perPage);
    }

    /**
     * Find subscription by ID or fail with eager loaded relations.
     *
     * @param int $id Record identifier.
     *
     * @return Subscription
     */
    public function findOrFail(int $id): Subscription
    {
        return Subscription::query()
            ->with([
                'tenant',
                'plan',
                'latestInvoice',
                'invoices',
                'subscriptionItems',
                'usageRecords',
                'lifecycleEvents',
            ])
            ->findOrFail($id);
    }

    /**
     * Create a new subscription via lifecycle (tenant + plan required in data).
     *
     * @param array<string, mixed> $data
     *
     * @return Subscription
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
     * Update subscription.
     *
     * @param Subscription $subscription The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     *
     * @return Subscription
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription->fresh([
            'tenant',
            'plan',
            'latestInvoice',
            'invoices',
            'subscriptionItems',
            'usageRecords',
            'lifecycleEvents',
        ]);
    }

    /**
     * Delete a single subscription.
     *
     * @param Subscription $subscription The model instance to delete.
     *
     * @return bool
     */
    public function delete(Subscription $subscription): bool
    {
        return $subscription->delete();
    }

    /**
     * Delete multiple subscriptions by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of deleted records.
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = Subscription::query()->whereIn('id', $ids)->get();
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
     * Filter by tenant.
     *
     * @param string $tenantId Tenant identifier.
     *
     * @return Collection<int, Subscription>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return Subscription::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param string $status Status value to filter by.
     *
     * @return Collection<int, Subscription>
     */
    public function getByStatus(string $status): Collection
    {
        return Subscription::query()->where('status', $status)->get();
    }

    /**
     * Filter by plan.
     *
     * @param int $planId Plan identifier.
     *
     * @return Collection<int, Subscription>
     */
    public function getByPlan(int $planId): Collection
    {
        return Subscription::query()->where('plan_id', $planId)->get();
    }

    /**
     * Cancel a subscription.
     *
     * @param Subscription $subscription The subscription to cancel.
     * @param string|null $reason Optional cancellation reason.
     *
     * @return Subscription
     */
    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        return $this->lifecycle->cancel($subscription, $reason, EventTriggeredBy::Admin);
    }

    /**
     * Renew a subscription billing period.
     *
     * @param Subscription $subscription The subscription to renew.
     *
     * @return Subscription
     */
    public function renew(Subscription $subscription): Subscription
    {
        return $this->lifecycle->renew($subscription, EventTriggeredBy::Admin);
    }

    /**
     * Upgrade subscription to a new plan.
     *
     * @param Subscription $subscription The subscription to upgrade.
     * @param Plan $plan Target plan.
     *
     * @return Subscription
     */
    public function upgrade(Subscription $subscription, Plan $plan): Subscription
    {
        return $this->lifecycle->upgrade($subscription, $plan, EventTriggeredBy::Admin);
    }

    /**
     * Downgrade subscription to a new plan.
     *
     * @param Subscription $subscription The subscription to downgrade.
     * @param Plan $plan Target plan.
     *
     * @return Subscription
     */
    public function downgrade(Subscription $subscription, Plan $plan): Subscription
    {
        return $this->lifecycle->downgrade($subscription, $plan, EventTriggeredBy::Admin);
    }

    /**
     * Reactivate a cancelled subscription.
     *
     * @param Subscription $subscription The subscription to reactivate.
     *
     * @return Subscription
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
