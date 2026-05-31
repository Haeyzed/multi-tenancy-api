<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreInvoiceRequest;
use App\Http\Requests\Central\UpdateInvoiceRequest;
use App\Http\Resources\Central\InvoiceResource;
use App\Models\Central\Invoice;
use App\Services\Central\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant billing invoices.
 */
class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $service,
    ) {}

    /**
     * Get paginated Invoice records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, InvoiceResource::collection($items));
    }

    /**
     * Create a new Invoice.
     *
     * @param  StoreInvoiceRequest  $request  Validated request payload.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new InvoiceResource($item), 'Invoice created successfully.');
    }

    /**
     * Find Invoice by route binding.
     *
     * @param  Invoice  $invoice  Invoice instance.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        return $this->success(new InvoiceResource($invoice));
    }

    /**
     * Update Invoice.
     *
     * @param  UpdateInvoiceRequest  $request  Validated request payload.
     * @param  Invoice  $invoice  Invoice instance.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $item = $this->service->update($invoice, $request->validated());

        return $this->updated(new InvoiceResource($item), 'Invoice updated successfully.');
    }

    /**
     * Delete Invoice.
     *
     * @param  Invoice  $invoice  Invoice instance.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->service->delete($invoice);

        return $this->deleted('Invoice deleted successfully.');
    }

    /**
     * List all open invoices past their due date.
     */
    public function getOverdue(): JsonResponse
    {
        $items = $this->service->getOverdue();

        return $this->success(InvoiceResource::collection($items));
    }

    /**
     * Mark an invoice as paid.
     *
     * @param  Request  $request  May include optional `payment_intent_id`.
     * @param  Invoice  $invoice  Invoice instance.
     */
    public function markAsPaid(Request $request, Invoice $invoice): JsonResponse
    {
        $item = $this->service->markAsPaid($invoice, $request->string('payment_intent_id')->toString() ?: null);

        return $this->success(new InvoiceResource($item), 'Invoice marked as paid.');
    }
}
