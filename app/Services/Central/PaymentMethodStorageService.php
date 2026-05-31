<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\PaymentMethodKind;
use App\Enums\Central\PaymentProvider;
use App\Models\Central\PaymentMethod;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;

/**
 * Persist saved payment methods from provider webhooks.
 */
class PaymentMethodStorageService
{
    /**
     * Store or update a payment method and link it to the subscription.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(
        Tenant $tenant,
        Subscription $subscription,
        PaymentProvider $provider,
        array $data,
    ): PaymentMethod {
        PaymentMethod::query()
            ->where('tenant_id', $tenant->id)
            ->update(['is_default' => false]);

        $method = PaymentMethod::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'provider' => $provider,
                'provider_method_id' => (string) $data['provider_method_id'],
            ],
            [
                'type' => PaymentMethodKind::from((string) ($data['type'] ?? 'card')),
                'last4' => $data['last4'] ?? null,
                'brand' => $data['brand'] ?? null,
                'exp_month' => $data['exp_month'] ?? null,
                'exp_year' => $data['exp_year'] ?? null,
                'is_default' => true,
                'billing_details' => $data['billing_details'] ?? null,
            ],
        );

        $subscription->update([
            'payment_provider' => $provider,
            'payment_provider_id' => $data['provider_customer_id'] ?? $subscription->payment_provider_id,
            'payment_method_id' => (string) $data['provider_method_id'],
        ]);

        return $method;
    }
}
