<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Enums\Central\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Models\Central\Invoice;
use App\Services\Central\PaymentFulfillmentService;
use App\Services\Central\PaymentMethodSetupService;
use App\Services\Payment\StripeGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stripe webhook handler for checkout and setup sessions.
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeGateway $gateway,
        private readonly PaymentFulfillmentService $fulfillment,
        private readonly PaymentMethodSetupService $setup,
    ) {}

    /**
     * Handle Stripe webhook events.
     */
    public function handle(Request $request): JsonResponse|Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (! $this->gateway->verifyWebhook($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        /** @var array<string, mixed> $event */
        $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if ($this->gateway->isSetupWebhook($event)) {
            $this->setup->handleStripeSetup($event);

            return response()->json(['received' => true]);
        }

        $invoiceId = $this->gateway->extractInvoiceIdFromWebhook($event);

        if ($invoiceId === null) {
            return response()->json(['received' => true]);
        }

        $providerPaymentId = $this->gateway->extractProviderPaymentIdFromWebhook($event);

        if ($providerPaymentId === null) {
            return response()->json(['message' => 'Missing payment reference.'], 422);
        }

        $invoice = Invoice::query()->findOrFail($invoiceId);

        $this->fulfillment->fulfill($invoice, $providerPaymentId, PaymentProvider::Stripe);

        $sessionId = $event['data']['object']['id'] ?? null;

        if ($sessionId !== null) {
            $details = $this->gateway->resolvePaymentDetails((string) $sessionId);

            if ($details !== null) {
                $this->setup->persistDetails($details, PaymentProvider::Stripe);
            }
        }

        return response()->json(['received' => true]);
    }
}
