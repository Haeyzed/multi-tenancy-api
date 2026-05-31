<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\InvoiceItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central InvoiceItem records and queries.
 */
class InvoiceItemService
{
    /**
     * Get all InvoiceItem records.
     *
     * @return Collection<int, InvoiceItem>
     */
    public function getAll(): Collection
    {
        return InvoiceItem::query()->get();
    }

    /**
     * Get paginated InvoiceItem records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, InvoiceItem>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return InvoiceItem::query()->paginate($perPage);
    }

    /**
     * Find InvoiceItem by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?InvoiceItem
    {
        return InvoiceItem::query()->find($id);
    }

    /**
     * Find InvoiceItem by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): InvoiceItem
    {
        return InvoiceItem::query()->findOrFail($id);
    }

    /**
     * Create a new InvoiceItem.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): InvoiceItem
    {
        return InvoiceItem::query()->create($data);
    }

    /**
     * Update InvoiceItem.
     *
     * @param  InvoiceItem  $invoiceItem  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(InvoiceItem $invoiceItem, array $data): InvoiceItem
    {
        $invoiceItem->query()->update($data);

        return $invoiceItem->fresh();
    }

    /**
     * Delete InvoiceItem.
     *
     * @param  InvoiceItem  $invoiceItem  The model instance to delete.
     */
    public function delete(InvoiceItem $invoiceItem): bool
    {
        return $invoiceItem->query()->delete() > 0;
    }

    /**
     * Filter by plan.
     *
     * @param  string  $planId  Plan UUID to filter by.
     * @return Collection<int, InvoiceItem>
     */
    public function getByPlan(string $planId): Collection
    {
        return InvoiceItem::query()->where('plan_id', $planId)->get();
    }

    /**
     * Filter by invoice.
     *
     * @param  string  $invoiceId  Invoice UUID to filter by.
     * @return Collection<int, InvoiceItem>
     */
    public function getByInvoice(string $invoiceId): Collection
    {
        return InvoiceItem::query()->where('invoice_id', $invoiceId)->get();
    }
}
