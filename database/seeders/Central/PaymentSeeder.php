<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\PaymentMethodType;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\PaymentStatus;
use App\Models\Central\Payment;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed payments in various statuses for demo tenants.
 */
class PaymentSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = [
            [
                'tenant' => 'acme-corp',
                'provider_payment_id' => 'pi_stripe_12345',
                'invoice_number' => 'INV-2026-0002',
                'amount' => 2_500_000,
                'status' => PaymentStatus::Succeeded,
                'payment_provider' => PaymentProvider::Stripe,
                'payment_method_type' => PaymentMethodType::Card,
                'payment_method_last4' => '4242',
            ],
            [
                'tenant' => 'beta-solutions',
                'provider_payment_id' => 'pi_stripe_54321',
                'invoice_number' => 'INV-2026-0003',
                'amount' => 67_500_000,
                'status' => PaymentStatus::Pending,
                'payment_provider' => PaymentProvider::Stripe,
                'payment_method_type' => PaymentMethodType::Card,
                'payment_method_last4' => '1234',
            ],
            [
                'tenant' => 'delta-works',
                'provider_payment_id' => 'pi_stripe_failed',
                'invoice_number' => 'INV-2026-0004',
                'amount' => 20_000_000,
                'status' => PaymentStatus::Failed,
                'payment_provider' => PaymentProvider::Stripe,
                'payment_method_type' => PaymentMethodType::Card,
                'payment_method_last4' => '0002',
                'failure_message' => 'Your card has expired.',
            ],
            [
                'tenant' => 'epsilon-ltd',
                'provider_payment_id' => 'pay_paddle_old',
                'invoice_number' => 'INV-2026-0005',
                'amount' => 2_500_000,
                'status' => PaymentStatus::Refunded,
                'payment_provider' => PaymentProvider::Paddle,
                'payment_method_type' => PaymentMethodType::BankTransfer,
                'payment_method_last4' => null,
                'refunded_amount' => 2_500_000,
            ],
            [
                'tenant' => 'acme-corp',
                'provider_payment_id' => 'pay_paypal_001',
                'invoice_number' => null,
                'amount' => 5_000_000,
                'status' => PaymentStatus::Succeeded,
                'payment_provider' => PaymentProvider::Paypal,
                'payment_method_type' => PaymentMethodType::Wallet,
                'payment_method_last4' => null,
            ],
        ];

        foreach ($payments as $payment) {
            Payment::query()->updateOrCreate(
                ['provider_payment_id' => $payment['provider_payment_id']],
                [
                    'tenant_id' => $this->tenant($payment['tenant'])->id,
                    'invoice_id' => $payment['invoice_number']
                        ? $this->invoice($payment['invoice_number'])->id
                        : null,
                    'amount' => $payment['amount'],
                    'currency' => 'NGN',
                    'status' => $payment['status'],
                    'payment_provider' => $payment['payment_provider'],
                    'payment_method_type' => $payment['payment_method_type'],
                    'payment_method_last4' => $payment['payment_method_last4'],
                    'failure_message' => $payment['failure_message'] ?? null,
                    'refunded_amount' => $payment['refunded_amount'] ?? 0,
                ],
            );
        }
    }
}
