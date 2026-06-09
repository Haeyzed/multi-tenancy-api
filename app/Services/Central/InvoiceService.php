<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\InvoiceStatus;
use App\Models\Central\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Invoice records and queries.
 */
class InvoiceService
{
    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'tenant',
        'subscription.plan',
    ];

    /**
     * Get all Invoice records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, Invoice>
     */
    public function getAll(?string $search = null): Collection
    {
        return Invoice::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated Invoice records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, Invoice>
     */
    /**
     * @param  list<string>  $status
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $status = [],
    ): LengthAwarePaginator {
        return Invoice::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterStatus($status)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find Invoice by ID.
     *
     * @param  string  $id  Record identifier.
     */
    public function find(string $id): ?Invoice
    {
        return Invoice::query()->find($id);
    }

    /**
     * Find Invoice by ID or fail.
     *
     * @param  string  $id  Record identifier.
     */
    public function findOrFail(string $id): Invoice
    {
        return Invoice::query()
            ->with([...self::LIST_RELATIONS, 'payments', 'invoiceItems'])
            ->findOrFail($id);
    }

    /**
     * Create a new Invoice.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Invoice
    {
        return Invoice::query()->create($data);
    }

    /**
     * Update Invoice.
     *
     * @param  Invoice  $invoice  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice->fresh(self::LIST_RELATIONS);
    }

    /**
     * Delete Invoice.
     *
     * @param  Invoice  $invoice  The model instance to delete.
     */
    public function delete(Invoice $invoice): bool
    {
        return (bool) $invoice->delete();
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, Invoice>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return Invoice::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param  string  $status  Status value to filter by.
     * @return Collection<int, Invoice>
     */
    public function getByStatus(string $status): Collection
    {
        return Invoice::query()->where('status', $status)->get();
    }

    /**
     * Filter by subscription.
     *
     * @param  string  $subscriptionId  Subscription UUID to filter by.
     * @return Collection<int, Invoice>
     */
    public function getBySubscription(string $subscriptionId): Collection
    {
        return Invoice::query()->where('subscription_id', $subscriptionId)->get();
    }

    /**
     * Get overdue invoices.
     *
     * @return Collection<int, Invoice>
     */
    public function getOverdue(): Collection
    {
        return Invoice::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->where('status', InvoiceStatus::Open->value)
            ->where('due_date', '<', now())
            ->latest()
            ->get();
    }

    /**
     * Mark an invoice as paid.
     *
     * @param  Invoice  $invoice  The invoice to mark as paid.
     * @param  string|null  $paymentIntentId  Optional payment provider intent identifier.
     */
    public function markAsPaid(Invoice $invoice, ?string $paymentIntentId = null): Invoice
    {
        $invoice->update([
            'status' => InvoiceStatus::Paid->value,
            'amount_paid' => $invoice->amount_due,
            'amount_remaining' => 0,
            'paid_at' => now(),
            'payment_intent_id' => $paymentIntentId ?? $invoice->payment_intent_id,
        ]);

        return $invoice->fresh(self::LIST_RELATIONS);
    }

    /**
     * Get invoices by tenant with items.
     *
     * @return Collection<int, Invoice>
     */
    public function getByTenantWithItems(string $tenantId): Collection
    {
        return Invoice::query()->with(['invoiceItems', 'payments'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * KPI card metrics for invoices.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $query = Invoice::query()->forTenant();

        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $overdue = (clone $query)
            ->where('status', InvoiceStatus::Open->value)
            ->where('due_date', '<', now())
            ->count();

        $overdueAmount = (int) (clone $query)
            ->where('status', InvoiceStatus::Open->value)
            ->where('due_date', '<', now())
            ->sum('amount_remaining');

        $outstandingAmount = (int) (clone $query)
            ->where('status', InvoiceStatus::Open->value)
            ->sum('amount_remaining');

        $collectedAmount = (int) (clone $query)
            ->where('status', InvoiceStatus::Paid->value)
            ->sum('amount_paid');

        return [
            ['key' => 'total', 'label' => 'Total Invoices', 'value' => (int) $counts->sum()],
            ['key' => 'open', 'label' => 'Open', 'value' => (int) ($counts[InvoiceStatus::Open->value] ?? 0)],
            ['key' => 'paid', 'label' => 'Paid', 'value' => (int) ($counts[InvoiceStatus::Paid->value] ?? 0)],
            ['key' => 'overdue', 'label' => 'Overdue', 'value' => $overdue],
            ['key' => 'overdue_amount', 'label' => 'Overdue Amount', 'value' => $overdueAmount],
            ['key' => 'outstanding_amount', 'label' => 'Outstanding Amount', 'value' => $outstandingAmount],
            ['key' => 'collected_amount', 'label' => 'Collected Amount', 'value' => $collectedAmount],
        ];
    }
}
