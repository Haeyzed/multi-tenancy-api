<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\PaymentMethodKind;
use App\Enums\Central\PaymentProvider;
use App\Models\Central\PaymentMethod;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed payment methods for demo tenants.
 */
class PaymentMethodSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'tenant' => 'acme-corp',
                'provider_method_id' => 'pm_stripe_67890',
                'provider' => PaymentProvider::Stripe,
                'type' => PaymentMethodKind::Card,
                'last4' => '4242',
                'brand' => 'Visa',
                'exp_month' => 12,
                'exp_year' => 2027,
                'is_default' => true,
                'billing_details' => ['name' => 'John Doe', 'email' => 'john@acme.com'],
            ],
            [
                'tenant' => 'acme-corp',
                'provider_method_id' => 'pm_stripe_99999',
                'provider' => PaymentProvider::Stripe,
                'type' => PaymentMethodKind::Card,
                'last4' => '1234',
                'brand' => 'Mastercard',
                'exp_month' => 6,
                'exp_year' => 2026,
                'is_default' => false,
                'billing_details' => ['name' => 'Jane Doe', 'email' => 'jane@acme.com'],
            ],
            [
                'tenant' => 'beta-solutions',
                'provider_method_id' => 'pm_stripe_09876',
                'provider' => PaymentProvider::Stripe,
                'type' => PaymentMethodKind::Card,
                'last4' => '1234',
                'brand' => 'Visa',
                'exp_month' => 3,
                'exp_year' => 2028,
                'is_default' => true,
                'billing_details' => ['name' => 'Sarah Chen', 'email' => 'sarah@beta.com'],
            ],
            [
                'tenant' => 'delta-works',
                'provider_method_id' => 'pm_stripe_expired',
                'provider' => PaymentProvider::Stripe,
                'type' => PaymentMethodKind::Card,
                'last4' => '0002',
                'brand' => 'Visa',
                'exp_month' => 1,
                'exp_year' => 2024,
                'is_default' => true,
                'billing_details' => ['name' => 'Lisa Wong', 'email' => 'lisa@delta.com'],
            ],
            [
                'tenant' => 'epsilon-ltd',
                'provider_method_id' => 'pm_paddle_old',
                'provider' => PaymentProvider::Paddle,
                'type' => PaymentMethodKind::BankAccount,
                'last4' => '7890',
                'brand' => 'ACH',
                'exp_month' => null,
                'exp_year' => null,
                'is_default' => true,
                'billing_details' => ['name' => 'Tom Hardy', 'email' => 'tom@epsilon.com'],
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                [
                    'tenant_id' => $this->tenant($method['tenant'])->id,
                    'provider_method_id' => $method['provider_method_id'],
                ],
                [
                    'provider' => $method['provider'],
                    'type' => $method['type'],
                    'last4' => $method['last4'],
                    'brand' => $method['brand'],
                    'exp_month' => $method['exp_month'],
                    'exp_year' => $method['exp_year'],
                    'is_default' => $method['is_default'],
                    'billing_details' => $method['billing_details'],
                ],
            );
        }
    }
}
