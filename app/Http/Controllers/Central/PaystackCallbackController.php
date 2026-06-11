<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\TenantResource;
use App\Models\Central\Tenant;
use App\Services\Payment\PaystackChargeHandlerService;
use App\Services\Payment\PaystackGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

/**
 * Paystack redirect callback after checkout (success URL).
 */
class PaystackCallbackController extends Controller
{
    public function __construct(
        private readonly PaystackGateway              $gateway,
        private readonly PaystackChargeHandlerService $handler,
    )
    {
    }

    /**
     * Verify a Paystack transaction and fulfill signup billing.
     *
     * Paystack redirects here with `?reference=` and `?trxref=` query params.
     * Browser requests are redirected to the frontend success page after verification.
     */
    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!is_string($reference) || $reference === '') {
            return $this->respond($request, null, 'Missing payment reference.', 422);
        }

        try {
            $charge = $this->gateway->verifyTransaction($reference);
            $result = $this->handler->handleSuccessfulCharge($charge);

            $tenant = isset($result['tenant_id'])
                ? Tenant::query()->with(['plan', 'domains', 'activeSubscription', 'paymentMethods'])->find($result['tenant_id'])
                : null;

            return $this->respond($request, [
                'verification' => $result,
                'tenant' => $tenant !== null ? new TenantResource($tenant) : null,
            ], 'Payment verified successfully.');
        } catch (RuntimeException $exception) {
            return $this->respond($request, null, $exception->getMessage(), 422, $reference);
        } catch (Throwable $exception) {
            report($exception);

            return $this->respond($request, null, 'Unable to verify Paystack payment.', 500, $reference);
        }
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function respond(
        Request $request,
        ?array  $data,
        string  $message,
        int     $errorStatus = 200,
        ?string $reference = null,
    ): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            if ($data === null) {
                return response()->json(['message' => $message], $errorStatus);
            }

            return $this->success($data, $message);
        }

        $frontendUrl = (string)config('payments.checkout.frontend_success_url');

        if ($frontendUrl === '') {
            if ($data === null) {
                return response()->json(['message' => $message], $errorStatus);
            }

            return $this->success($data, $message);
        }

        $verification = is_array($data['verification'] ?? null) ? $data['verification'] : [];
        $tenantResource = $data['tenant'] ?? null;
        $tenantId = $verification['tenant_id']
            ?? (is_object($tenantResource) ? $tenantResource->id : null);

        $query = array_filter([
            'status' => $data === null ? 'error' : 'success',
            'message' => $data === null ? $message : null,
            'reference' => $reference,
            'tenant_id' => is_string($tenantId) ? $tenantId : null,
            'purpose' => is_string($verification['purpose'] ?? null) ? $verification['purpose'] : null,
        ], fn($value) => $value !== null && $value !== '');

        return redirect()->away($frontendUrl . '?' . http_build_query($query));
    }
}
