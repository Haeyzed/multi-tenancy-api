<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreInvoiceItemRequest;
use App\Http\Requests\Central\UpdateInvoiceItemRequest;
use App\Http\Resources\Central\InvoiceItemResource;
use App\Models\Central\InvoiceItem;
use App\Services\Central\InvoiceItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Line items on tenant invoices.
 */
class InvoiceItemController extends Controller
{
    public function __construct(
        private readonly InvoiceItemService $service,
    ) {}

    /**
     * Get paginated InvoiceItem records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, InvoiceItemResource::collection($items));
    }

    /**
     * Create a new InvoiceItem.
     *
     * @param  StoreInvoiceItemRequest  $request  Validated request payload.
     */
    public function store(StoreInvoiceItemRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new InvoiceItemResource($item), 'Invoice item created successfully.');
    }

    /**
     * Find InvoiceItem by route binding.
     *
     * @param  InvoiceItem  $invoiceItem  InvoiceItem instance.
     */
    public function show(InvoiceItem $invoiceItem): JsonResponse
    {
        return $this->success(new InvoiceItemResource($invoiceItem));
    }

    /**
     * Update InvoiceItem.
     *
     * @param  UpdateInvoiceItemRequest  $request  Validated request payload.
     * @param  InvoiceItem  $invoiceItem  InvoiceItem instance.
     */
    public function update(UpdateInvoiceItemRequest $request, InvoiceItem $invoiceItem): JsonResponse
    {
        $item = $this->service->update($invoiceItem, $request->validated());

        return $this->updated(new InvoiceItemResource($item), 'Invoice item updated successfully.');
    }

    /**
     * Delete InvoiceItem.
     *
     * @param  InvoiceItem  $invoiceItem  InvoiceItem instance.
     */
    public function destroy(InvoiceItem $invoiceItem): JsonResponse
    {
        $this->service->delete($invoiceItem);

        return $this->deleted('Invoice item deleted successfully.');
    }
}
