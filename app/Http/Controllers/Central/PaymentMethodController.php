<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePaymentMethodRequest;
use App\Http\Requests\Central\UpdatePaymentMethodRequest;
use App\Http\Resources\Central\PaymentMethodResource;
use App\Models\Central\PaymentMethod;
use App\Services\Central\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant stored payment methods.
 */
class PaymentMethodController extends Controller
{
    public function __construct(
        private readonly PaymentMethodService $service,
    )
    {
    }

    /**
     * Get paginated PaymentMethod records.
     *
     * @param Request $request Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');

        $items = $this->service->getPaginated($perPage, $search);

        return $this->paginated($items, PaymentMethodResource::collection($items), 'Payment methods retrieved successfully.');
    }

    /**
     * Create a new PaymentMethod.
     *
     * @param StorePaymentMethodRequest $request Validated request payload.
     */
    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new PaymentMethodResource($item), 'Payment method created successfully.');
    }

    /**
     * Find PaymentMethod by route binding.
     *
     * @param PaymentMethod $paymentMethod PaymentMethod instance.
     */
    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        return $this->success(new PaymentMethodResource($paymentMethod), 'Payment method retrieved successfully.');
    }

    /**
     * Update PaymentMethod.
     *
     * @param UpdatePaymentMethodRequest $request Validated request payload.
     * @param PaymentMethod $paymentMethod PaymentMethod instance.
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $item = $this->service->update($paymentMethod, $request->validated());

        return $this->updated(new PaymentMethodResource($item), 'Payment method updated successfully.');
    }

    /**
     * Delete PaymentMethod.
     *
     * @param PaymentMethod $paymentMethod PaymentMethod instance.
     */
    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $this->service->delete($paymentMethod);

        return $this->deleted('Payment method deleted successfully.');
    }

    /**
     * Set a payment method as the tenant default.
     *
     * @param PaymentMethod $paymentMethod PaymentMethod instance.
     */
    public function setDefault(PaymentMethod $paymentMethod): JsonResponse
    {
        $item = $this->service->setDefault($paymentMethod);

        return $this->success(new PaymentMethodResource($item), 'Payment method set as default.');
    }
}
