<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\PaymentStatus;
use App\Enums\Central\SubscriptionStatus;
use App\Enums\Central\SupportTicketPriority;
use App\Enums\Central\SupportTicketStatus;
use App\Enums\Central\TenantStatus;
use App\Models\Central\Activity;
use App\Models\Central\ErrorLog;
use App\Models\Central\Payment;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Models\Central\TenantSupportTicket;
use App\Models\Central\User;
use Illuminate\Support\Collection;

/**
 * Aggregated platform dashboard metrics, charts, and recent activity.
 */
class DashboardService
{
    private const CHART_DAYS = 30;

    private const RECENT_LIMIT = 8;

    /**
     * Build a permission-aware dashboard overview for the authenticated user.
     *
     * @return array<string, mixed>
     */
    public function getOverview(User $user): array
    {
        return [
            'cards' => $this->getCards($user),
            'charts' => $this->getCharts($user),
            'recent' => $this->getRecent($user),
        ];
    }

    /**
     * @return list<array{key: string, label: string, value: int|string, module: string}>
     */
    private function getCards(User $user): array
    {
        $cards = [];

        if ($user->can('tenants.view')) {
            $tenantCounts = Tenant::query()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $onTrial = Tenant::query()
                ->whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>=', now())
                ->count();

            $cards[] = [
                'key' => 'tenants_total',
                'label' => 'Total Tenants',
                'value' => (int) $tenantCounts->sum(),
                'module' => 'tenants',
            ];
            $cards[] = [
                'key' => 'tenants_active',
                'label' => 'Active Tenants',
                'value' => (int) ($tenantCounts[TenantStatus::Active->value] ?? 0),
                'module' => 'tenants',
            ];
            $cards[] = [
                'key' => 'tenants_on_trial',
                'label' => 'Tenants on Trial',
                'value' => $onTrial,
                'module' => 'tenants',
            ];
        }

        if ($user->can('billing.view')) {
            $subscriptionCounts = Subscription::query()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $revenueCollected = (int) Payment::query()
                ->where('status', PaymentStatus::Succeeded)
                ->sum('amount');

            $cards[] = [
                'key' => 'subscriptions_active',
                'label' => 'Active Subscriptions',
                'value' => (int) ($subscriptionCounts[SubscriptionStatus::Active->value] ?? 0),
                'module' => 'billing',
            ];
            $cards[] = [
                'key' => 'subscriptions_trialing',
                'label' => 'Trialing Subscriptions',
                'value' => (int) ($subscriptionCounts[SubscriptionStatus::Trialing->value] ?? 0),
                'module' => 'billing',
            ];
            $cards[] = [
                'key' => 'revenue_collected',
                'label' => 'Revenue Collected',
                'value' => $this->formatMoney($revenueCollected),
                'module' => 'billing',
            ];
        }

        if ($user->can('users.view')) {
            $cards[] = [
                'key' => 'users_total',
                'label' => 'Platform Users',
                'value' => User::query()->count(),
                'module' => 'users',
            ];
            $cards[] = [
                'key' => 'users_active',
                'label' => 'Active Users',
                'value' => User::query()->where('is_active', true)->count(),
                'module' => 'users',
            ];
        }

        if ($user->can('support.view')) {
            $openStatuses = [
                SupportTicketStatus::Open->value,
                SupportTicketStatus::InProgress->value,
                SupportTicketStatus::WaitingCustomer->value,
            ];

            $openTickets = TenantSupportTicket::query()
                ->whereIn('status', $openStatuses)
                ->count();

            $urgentOpen = TenantSupportTicket::query()
                ->whereIn('status', $openStatuses)
                ->where('priority', SupportTicketPriority::Urgent->value)
                ->count();

            $cards[] = [
                'key' => 'tickets_open',
                'label' => 'Open Tickets',
                'value' => $openTickets,
                'module' => 'support',
            ];
            $cards[] = [
                'key' => 'tickets_urgent',
                'label' => 'Urgent Tickets',
                'value' => $urgentOpen,
                'module' => 'support',
            ];
        }

        if ($user->can('monitoring.view')) {
            $unresolvedErrors = ErrorLog::query()
                ->whereNull('resolved_at')
                ->count();

            $cards[] = [
                'key' => 'errors_unresolved',
                'label' => 'Unresolved Errors',
                'value' => $unresolvedErrors,
                'module' => 'monitoring',
            ];
        }

        return $cards;
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function getCharts(User $user): array
    {
        $charts = [];
        $startDate = now()->subDays(self::CHART_DAYS)->startOfDay();

        if ($user->can('tenants.view')) {
            $charts['tenant_growth'] = $this->fillDateSeries(
                Tenant::query()
                    ->where('created_at', '>=', $startDate)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date'),
                'count',
            );

            $tenantStatus = Tenant::query()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $charts['tenant_status'] = $tenantStatus
                ->map(fn (int $count, string $status): array => [
                    'status' => $status,
                    'label' => ucfirst($status),
                    'count' => $count,
                ])
                ->values()
                ->all();
        }

        if ($user->can('billing.view')) {
            $charts['revenue_over_time'] = $this->fillDateSeries(
                Payment::query()
                    ->where('status', PaymentStatus::Succeeded)
                    ->where('created_at', '>=', $startDate)
                    ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('revenue', 'date'),
                'revenue',
            );

            $subscriptionStatus = Subscription::query()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $charts['subscription_status'] = $subscriptionStatus
                ->map(fn (int $count, string $status): array => [
                    'status' => $status,
                    'label' => str_replace('_', ' ', ucfirst($status)),
                    'count' => $count,
                ])
                ->values()
                ->all();
        }

        return $charts;
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function getRecent(User $user): array
    {
        $recent = [];

        if ($user->can('tenants.view')) {
            $recent['tenants'] = Tenant::query()
                ->with('plan:id,name')
                ->latest('created_at')
                ->limit(self::RECENT_LIMIT)
                ->get()
                ->map(fn (Tenant $tenant): array => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'status' => $tenant->status->value,
                    'plan' => $tenant->plan?->name,
                    'owner_email' => $tenant->owner_email,
                    'created_at' => $tenant->created_at?->toIso8601String(),
                ])
                ->all();
        }

        if ($user->can('billing.view')) {
            $recent['subscriptions'] = Subscription::query()
                ->with(['tenant:id,name', 'plan:id,name'])
                ->latest('created_at')
                ->limit(self::RECENT_LIMIT)
                ->get()
                ->map(fn (Subscription $subscription): array => [
                    'id' => $subscription->id,
                    'tenant' => $subscription->tenant?->name,
                    'plan' => $subscription->plan?->name,
                    'status' => $subscription->status->value,
                    'billing_cycle' => $subscription->billing_cycle->value,
                    'created_at' => $subscription->created_at?->toIso8601String(),
                ])
                ->all();

            $recent['payments'] = Payment::query()
                ->with('tenant:id,name')
                ->latest('created_at')
                ->limit(self::RECENT_LIMIT)
                ->get()
                ->map(fn (Payment $payment): array => [
                    'id' => $payment->id,
                    'tenant' => $payment->tenant?->name,
                    'amount' => $this->formatMoney((int) $payment->amount, $payment->currency),
                    'status' => $payment->status->value,
                    'provider' => $payment->payment_provider->value,
                    'created_at' => $payment->created_at?->toIso8601String(),
                ])
                ->all();
        }

        if ($user->can('platform.view')) {
            $recent['activities'] = Activity::query()
                ->latest('created_at')
                ->limit(self::RECENT_LIMIT)
                ->get()
                ->map(fn (Activity $activity): array => [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'event' => $activity->event,
                    'subject_type' => $activity->subject_type,
                    'created_at' => $activity->created_at?->toIso8601String(),
                ])
                ->all();
        }

        if ($user->can('support.view')) {
            $openStatuses = [
                SupportTicketStatus::Open->value,
                SupportTicketStatus::InProgress->value,
                SupportTicketStatus::WaitingCustomer->value,
            ];

            $recent['support_tickets'] = TenantSupportTicket::query()
                ->with('tenant:id,name')
                ->whereIn('status', $openStatuses)
                ->latest('updated_at')
                ->limit(self::RECENT_LIMIT)
                ->get()
                ->map(fn (TenantSupportTicket $ticket): array => [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'tenant' => $ticket->tenant?->name,
                    'status' => $ticket->status->value,
                    'priority' => $ticket->priority->value,
                    'updated_at' => $ticket->updated_at?->toIso8601String(),
                ])
                ->all();
        }

        return $recent;
    }

    /**
     * @param  Collection<int|string, int|string>  $values
     * @return list<array{date: string, count?: int, revenue?: int}>
     */
    private function fillDateSeries(Collection $values, string $valueKey): array
    {
        $series = [];
        $cursor = now()->subDays(self::CHART_DAYS - 1)->startOfDay();
        $end = now()->startOfDay();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            $series[] = [
                'date' => $dateKey,
                $valueKey => (int) ($values[$dateKey] ?? 0),
            ];
            $cursor = $cursor->copy()->addDay();
        }

        return $series;
    }

    private function formatMoney(int $amountMinor, string $currency = 'NGN'): string
    {
        $major = $amountMinor / 100;

        return sprintf('%s %.2f', strtoupper($currency), $major);
    }
}
