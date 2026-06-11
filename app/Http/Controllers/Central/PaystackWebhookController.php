<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaystackChargeHandlerService;
use App\Services\Payment\PaystackGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paystack webhook handler for charges and trial setup.
 */
class PaystackWebhookController extends Controller
{
    public function __construct(
        private readonly PaystackGateway              $gateway,
        private readonly PaystackChargeHandlerService $handler,
    )
    {
    }

    /**
     * Handle Paystack webhook events.
     */
    public function handle(Request $request): JsonResponse|Response
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (!$this->gateway->verifyWebhook($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        /** @var array<string, mixed> $event */
        $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if (($event['event'] ?? null) !== 'charge.success') {
            return response()->json(['received' => true]);
        }

        /** @var array<string, mixed> $charge */
        $charge = $event['data'] ?? [];

        if ($charge === []) {
            return response()->json(['message' => 'Missing charge data.'], 422);
        }

        $this->handler->handleSuccessfulCharge($charge);

        return response()->json(['received' => true]);
    }
}
