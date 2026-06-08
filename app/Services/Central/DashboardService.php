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
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Aggregated platform dashboard metrics, charts, and recent activity.
 */
class DashboardService
{
    private const DEFAULT_RANGE_DAYS = 30;

    private const RECENT_LIMIT = 8;

    /**
     * Build a permission-aware dashboard overview for the authenticated user.
     *
     * @return array<string, mixed>
     */
    public function getOverview(
        User $user,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        [$start, $end] = $this->resolveDateRange($startDate, $endDate);

        return [
            'range' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            'cards' => $this->getCards($user, $start, $end),
            'charts' => $this->getCharts($user, $start, $end),
            'recent' => $this->getRecent($user, $start, $end),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        $end = $endDate !== null
            ? Carbon::parse($endDate)->endOfDay()
            : now()->endOfDay();

        $start = $startDate !== null
            ? Carbon::parse($startDate)->startOfDay()
            : $end->copy()->subDays(self::DEFAULT_RANGE_DAYS - 1)->startOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    /**
     * @return list<array{key: string, label: string, value: int|string, module: string}>
     */
    private function getCards(User $user, Carbon $start, Carbon $end): array
    {
        $cards = [];

        if ($user->can('tenants.view')) {
            $tenantQuery = Tenant::query()->whereBetween('created_at', [$start, $end]);

            $tenantCounts = (clone $tenantQuery)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $onTrial = (clone $tenantQuery)
                ->whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>=', now())
                ->count();

            $cards[] = [
                'key' => 'tenants_total',
                'label' => 'New Tenants',
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
            $subscriptionQuery = Subscription::query()->whereBetween('created_at', [$start, $end]);

            $subscriptionCounts = (clone $subscriptionQuery)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $revenueCollected = (int) Payment::query()
                ->where('status', PaymentStatus::Succeeded)
                ->whereBetween('created_at', [$start, $end])
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
            $userQuery = User::query()->whereBetween('created_at', [$start, $end]);

            $cards[] = [
                'key' => 'users_total',
                'label' => 'New Users',
                'value' => (clone $userQuery)->count(),
                'module' => 'users',
            ];
            $cards[] = [
                'key' => 'users_active',
                'label' => 'Active Users',
                'value' => (clone $userQuery)->where('is_active', true)->count(),
                'module' => 'users',
            ];
        }

        if ($user->can('support.view')) {
            $openStatuses = [
                SupportTicketStatus::Open->value,
                SupportTicketStatus::InProgress->value,
                SupportTicketStatus::WaitingCustomer->value,
            ];

            $ticketQuery = TenantSupportTicket::query()
                ->whereBetween('created_at', [$start, $end]);

            $openTickets = (clone $ticketQuery)
                ->whereIn('status', $openStatuses)
                ->count();

            $urgentOpen = (clone $ticketQuery)
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
                ->whereBetween('created_at', [$start, $end])
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
    private function getCharts(User $user, Carbon $start, Carbon $end): array
    {
        $charts = [];

        if ($user->can('tenants.view')) {
            $charts['tenant_growth'] = $this->fillDateSeries(
                Tenant::query()
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date'),
                'count',
                $start,
                $end,
            );

            $tenantStatus = Tenant::query()
                ->whereBetween('created_at', [$start, $end])
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
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('revenue', 'date'),
                'revenue',
                $start,
                $end,
            );

            $subscriptionStatus = Subscription::query()
                ->whereBetween('created_at', [$start, $end])
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
    private function getRecent(User $user, Carbon $start, Carbon $end): array
    {
        $recent = [];

        if ($user->can('tenants.view')) {
            $recent['tenants'] = Tenant::query()
                ->with('plan:id,name')
                ->whereBetween('created_at', [$start, $end])
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
                ->whereBetween('created_at', [$start, $end])
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
                ->whereBetween('created_at', [$start, $end])
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
                ->whereBetween('created_at', [$start, $end])
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
                ->whereBetween('updated_at', [$start, $end])
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
    private function fillDateSeries(
        Collection $values,
        string $valueKey,
        Carbon $start,
        Carbon $end,
    ): array {
        $series = [];
        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();

        while ($cursor->lte($endDay)) {
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
