<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePaymentRequest;
use App\Http\Requests\Central\UpdatePaymentRequest;
use App\Http\Resources\Central\PaymentResource;
use App\Models\Central\Payment;
use App\Services\Central\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant payment transactions.
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $service,
    ) {}

    /**
     * Get paginated Payment records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');

        $items = $this->service->getPaginated($perPage, $search);

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
     * @param  StorePaymentRequest  $request  Validated request payload.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PaymentResource($item), 'Payment created successfully.');
    }

    /**
     * Find Payment by route binding.
     *
     * @param  Payment  $payment  Payment instance.
     */
    public function show(Payment $payment): JsonResponse
    {
        return $this->success(new PaymentResource($payment), 'Payment retrieved successfully.');
    }

    /**
     * Update Payment.
     *
     * @param  UpdatePaymentRequest  $request  Validated request payload.
     * @param  Payment  $payment  Payment instance.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $item = $this->service->update($payment, $request->validated());

        return $this->updated(new PaymentResource($item), 'Payment updated successfully.');
    }

    /**
     * Delete Payment.
     *
     * @param  Payment  $payment  Payment instance.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $this->service->delete($payment);

        return $this->deleted('Payment deleted successfully.');
    }

    /**
     * Refund a payment partially or in full.
     *
     * @param  Request  $request  Must include `amount` in smallest currency unit.
     * @param  Payment  $payment  Payment instance.
     */
    public function refund(Request $request, Payment $payment): JsonResponse
    {
        $item = $this->service->refund($payment, $request->integer('amount'));

        return $this->success(new PaymentResource($item), 'Payment refunded.');
    }
}
