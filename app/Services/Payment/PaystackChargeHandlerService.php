<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Enums\Central\PaymentProvider;
use App\Models\Central\Invoice;
use App\Models\Central\PaymentMethod;
use App\Models\Central\Tenant;
use App\Services\Central\PaymentFulfillmentService;
use App\Services\Central\PaymentMethodSetupService;
use App\Services\Central\PaymentRecordingService;
use App\Support\OnboardingNotes;
use RuntimeException;

/**
 * Process successful Paystack charge payloads (webhook or verify callback).
 */
class PaystackChargeHandlerService
{
    public function __construct(
        private readonly PaystackGateway $gateway,
        private readonly PaymentFulfillmentService $fulfillment,
        private readonly PaymentMethodSetupService $setup,
        private readonly PaymentRecordingService $payments,
    ) {}

    /**
     * @param  array<string, mixed>  $charge  Paystack charge object (transaction verify `data` or webhook `data`).
     * @return array{
     *     purpose: string,
     *     reference: string,
     *     tenant_id: string|null,
     *     subscription_id: string|null,
     *     invoice_id: string|null,
     *     payment_method_saved: bool,
     *     tenant_activated: bool,
     *     payment_recorded: bool
     * }
     */
    public function handleSuccessfulCharge(array $charge): array
    {
        $reference = (string) ($charge['reference'] ?? '');

        if ($reference === '') {
            throw new RuntimeException('Paystack charge is missing a transaction reference.');
        }

        $event = ['event' => 'charge.success', 'data' => $charge];
        $metadata = $charge['metadata'] ?? [];
        $purpose = (string) ($metadata['purpose'] ?? 'payment');
        $tenantId = isset($metadata['tenant_id']) ? (string) $metadata['tenant_id'] : null;
        $subscriptionId = isset($metadata['subscription_id']) ? (string) $metadata['subscription_id'] : null;
        $methodData = $this->gateway->extractPaymentMethodFromWebhook($event);

        if ($this->gateway->isSetupWebhook($event)) {
            $alreadySaved = $tenantId !== null
                && PaymentMethod::query()->where('tenant_id', $tenantId)->exists();

            if (! $alreadySaved) {
                $this->setup->handlePaystackSetup($event);
            }

            $paymentRecorded = false;

            if ($tenantId !== null) {
                $tenant = Tenant::query()->find($tenantId);

                if ($tenant !== null) {
                    $this->payments->recordSucceeded(
                        $tenant,
                        PaymentProvider::Paystack,
                        $reference,
                        (int) ($charge['amount'] ?? config('payments.trial_setup_amount', 10_000)),
                        strtoupper((string) ($charge['currency'] ?? 'NGN')),
                        null,
                        $methodData,
                    );

                    $meta = $tenant->meta ?? [];
                    $meta['onboarding_notes'] = OnboardingNotes::compose(
                        $meta['onboarding_notes'] ?? null,
                        OnboardingNotes::trialVerificationPaid(
                            PaymentProvider::Paystack->value,
                            $reference,
                            (int) ($charge['amount'] ?? config('payments.trial_setup_amount', 10_000)),
                            strtoupper((string) ($charge['currency'] ?? 'NGN')),
                        ),
                    );
                    $tenant->update(['meta' => $meta]);

                    $paymentRecorded = true;
                }
            }

            return [
                'purpose' => 'trial_setup',
                'reference' => $reference,
                'tenant_id' => $tenantId,
                'subscription_id' => $subscriptionId,
                'invoice_id' => null,
                'payment_method_saved' => true,
                'tenant_activated' => $tenantId !== null
                    && Tenant::query()->whereKey($tenantId)->value('status') === 'active',
                'payment_recorded' => $paymentRecorded,
            ];
        }

        $invoiceId = $this->gateway->extractInvoiceIdFromWebhook($event);

        if ($invoiceId === null) {
            throw new RuntimeException('Unable to resolve invoice for this Paystack transaction.');
        }

        $invoice = Invoice::query()->findOrFail($invoiceId);
        $wasPending = $invoice->status->value !== 'paid';

        $this->fulfillment->fulfill(
            $invoice,
            $reference,
            PaymentProvider::Paystack,
            false,
            $methodData,
        );

        if ($methodData !== null) {
            $this->setup->persistDetails($methodData, PaymentProvider::Paystack);
        }

        return [
            'purpose' => $purpose,
            'reference' => $reference,
            'tenant_id' => $tenantId ?? $invoice->tenant_id,
            'subscription_id' => $subscriptionId ?? $invoice->subscription_id,
            'invoice_id' => $invoiceId,
            'payment_method_saved' => $methodData !== null,
            'tenant_activated' => $wasPending,
            'payment_recorded' => true,
        ];
    }
}
