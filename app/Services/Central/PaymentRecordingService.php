<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\PaymentProvider;
use App\Enums\Central\PaymentStatus;
use App\Models\Central\Invoice;
use App\Models\Central\Payment;
use App\Models\Central\Tenant;

/**
 * Persist payment transactions with card metadata from providers.
 */
class PaymentRecordingService
{
    /**
     * @param array<string, mixed>|null $card
     */
    public function recordSucceeded(
        Tenant          $tenant,
        PaymentProvider $provider,
        string          $providerPaymentId,
        int             $amount,
        string          $currency,
        ?Invoice        $invoice = null,
        ?array          $card = null,
    ): Payment
    {
        return Payment::query()->updateOrCreate(
            [
                'provider_payment_id' => $providerPaymentId,
            ],
            [
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoice?->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => PaymentStatus::Succeeded,
                'payment_provider' => $provider,
                ...$this->cardColumns($card),
            ],
        );
    }

    /**
     * @param array<string, mixed>|null $card
     * @return array<string, string|null>
     */
    public function cardColumns(?array $card): array
    {
        if ($card === null) {
            return [
                'payment_method_type' => null,
                'payment_method_last4' => null,
                'payment_method_brand' => null,
            ];
        }

        return [
            'payment_method_type' => isset($card['type']) ? (string)$card['type'] : 'card',
            'payment_method_last4' => isset($card['last4']) ? (string)$card['last4'] : null,
            'payment_method_brand' => isset($card['brand']) ? (string)$card['brand'] : null,
        ];
    }
}
