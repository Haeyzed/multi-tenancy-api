<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\TenantResource;
use App\Models\Central\Tenant;
use App\Services\Payment\PaystackChargeHandlerService;
use App\Services\Payment\PaystackGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

/**
 * Paystack redirect callback after checkout (success URL).
 */
class PaystackCallbackController extends Controller
{
    public function __construct(
        private readonly PaystackGateway $gateway,
        private readonly PaystackChargeHandlerService $handler,
    ) {}

    /**
     * Verify a Paystack transaction and fulfill signup billing.
     *
     * Paystack redirects here with `?reference=` and `?trxref=` query params.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (! is_string($reference) || $reference === '') {
            return response()->json(['message' => 'Missing payment reference.'], 422);
        }

        try {
            $charge = $this->gateway->verifyTransaction($reference);
            $result = $this->handler->handleSuccessfulCharge($charge);

            $tenant = isset($result['tenant_id'])
                ? Tenant::query()->with(['plan', 'domains', 'activeSubscription', 'paymentMethods'])->find($result['tenant_id'])
                : null;

            return $this->success([
                'verification' => $result,
                'tenant' => $tenant !== null ? new TenantResource($tenant) : null,
            ], 'Payment verified successfully.');
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Unable to verify Paystack payment.'], 500);
        }
    }
}
