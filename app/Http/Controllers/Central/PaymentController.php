<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\BulkDeletePaymentsRequest;
use App\Http\Requests\Central\RefundPaymentRequest;
use App\Http\Requests\Central\StorePaymentRequest;
use App\Http\Requests\Central\UpdatePaymentRequest;
use App\Http\Resources\Central\PaymentResource;
use App\Models\Central\Payment;
use App\Services\Central\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant payment transactions.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the PaymentService layer.
 */
class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PaymentService $service
     */
    public function __construct(
        private readonly PaymentService $service,
    ) {}

    /**
     * Get paginated payment records.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $status = $request->query('status');

        $items = $this->service->getPaginated($perPage, $search, $status);

        return $this->paginated($items, PaymentResource::collection($items), 'Payments retrieved successfully.');
    }

    /**
     * KPI card metrics for payments.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Payment KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new Payment.
     *
     * @param StorePaymentRequest $request Validated request payload.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PaymentResource($item), 'Payment created successfully.');
    }

    /**
     * Find payment by route binding.
     *
     * @param Payment $payment Payment instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(Payment $payment): JsonResponse
    {
        $item = $this->service->findOrFail($payment->id);

        return $this->success(new PaymentResource($item), 'Payment retrieved successfully.');
    }

    /**
     * Update Payment.
     *
     * @param UpdatePaymentRequest $request Validated request payload.
     * @param Payment $payment Payment instance.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $item = $this->service->update($payment, $request->validated());

        return $this->updated(new PaymentResource($item), 'Payment updated successfully.');
    }

    /**
     * Delete Payment.
     *
     * @param Payment $payment Payment instance.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $this->service->delete($payment);

        return $this->deleted('Payment deleted successfully.');
    }

    /**
     * Delete multiple payments in one request.
     *
     * @param BulkDeletePaymentsRequest $request
     *
     * @return JsonResponse
     */
    public function bulkDestroy(BulkDeletePaymentsRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} payment(s) deleted successfully.",
        );
    }

    /**
     * Refund a payment partially or in full.
     *
     * @param Request $request Must include `amount` in smallest currency unit.
     * @param Payment $payment Payment instance.
     */
    public function refund(RefundPaymentRequest $request, Payment $payment): JsonResponse
    {
        $amount = $request->validated('amount');
        $item = $this->service->refund($payment, is_int($amount) ? $amount : null);

        return $this->success(new PaymentResource($item), 'Payment refunded.');
    }
}
