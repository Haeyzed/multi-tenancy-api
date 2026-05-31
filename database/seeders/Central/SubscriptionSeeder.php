<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\SubscriptionStatus;
use App\Models\Central\Subscription;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed tenant subscriptions without latest_invoice_id (linked after invoices exist).
 */
class SubscriptionSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = [
            [
                'tenant' => 'acme-corp',
                'plan' => 'starter',
                'status' => SubscriptionStatus::Active,
                'billing_cycle' => BillingCycle::Monthly,
                'current_period_start' => now()->subDays(15),
                'current_period_end' => now()->addDays(15),
                'trial_ends_at' => now()->subDays(45),
                'payment_provider' => PaymentProvider::Stripe,
                'payment_provider_id' => 'sub_stripe_12345',
                'payment_method_id' => 'pm_stripe_67890',
            ],
            [
                'tenant' => 'beta-solutions',
                'plan' => 'professional',
                'status' => SubscriptionStatus::Active,
                'billing_cycle' => BillingCycle::Yearly,
                'current_period_start' => now()->subDays(80),
                'current_period_end' => now()->addDays(285),
                'trial_ends_at' => now()->subDays(90),
                'payment_provider' => PaymentProvider::Stripe,
                'payment_provider_id' => 'sub_stripe_54321',
                'payment_method_id' => 'pm_stripe_09876',
            ],
            [
                'tenant' => 'gamma-innovations',
                'plan' => 'starter',
                'status' => SubscriptionStatus::Trialing,
                'billing_cycle' => BillingCycle::Monthly,
                'current_period_start' => now()->subDays(4),
                'current_period_end' => now()->addDays(10),
                'trial_ends_at' => now()->addDays(10),
                'payment_provider' => PaymentProvider::Stripe,
                'payment_provider_id' => null,
                'payment_method_id' => null,
            ],
            [
                'tenant' => 'delta-works',
                'plan' => 'enterprise',
                'status' => SubscriptionStatus::PastDue,
                'billing_cycle' => BillingCycle::Monthly,
                'current_period_start' => now()->subDays(35),
                'current_period_end' => now()->subDays(5),
                'trial_ends_at' => now()->subDays(120),
                'payment_provider' => PaymentProvider::Stripe,
                'payment_provider_id' => 'sub_stripe_pastdue',
                'payment_method_id' => 'pm_stripe_expired',
            ],
            [
                'tenant' => 'epsilon-ltd',
                'plan' => 'starter',
                'status' => SubscriptionStatus::Cancelled,
                'billing_cycle' => BillingCycle::Monthly,
                'current_period_start' => now()->subDays(90),
                'current_period_end' => now()->subDays(60),
                'trial_ends_at' => now()->subDays(200),
                'cancelled_at' => now()->subDays(60),
                'cancellation_reason' => 'Switched to competitor',
                'payment_provider' => PaymentProvider::Paddle,
                'payment_provider_id' => 'sub_paddle_old',
                'payment_method_id' => null,
            ],
        ];

        foreach ($subscriptions as $subscription) {
            $tenant = $this->tenant($subscription['tenant']);

            Subscription::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'plan_id' => $this->plan($subscription['plan'])->id,
                    'status' => $subscription['status'],
                    'billing_cycle' => $subscription['billing_cycle'],
                    'current_period_start' => $subscription['current_period_start'],
                    'current_period_end' => $subscription['current_period_end'],
                    'trial_ends_at' => $subscription['trial_ends_at'],
                    'cancelled_at' => $subscription['cancelled_at'] ?? null,
                    'cancellation_reason' => $subscription['cancellation_reason'] ?? null,
                    'payment_provider' => $subscription['payment_provider'],
                    'payment_provider_id' => $subscription['payment_provider_id'],
                    'payment_method_id' => $subscription['payment_method_id'],
                    'latest_invoice_id' => null,
                ],
            );
        }
    }
}
