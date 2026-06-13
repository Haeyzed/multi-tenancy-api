<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\InvoiceStatus;
use App\Models\Central\Invoice;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Central tenant billing invoice records and queries.
 *
 * Encapsulates all business logic for invoice management, including
 * creation, updates, pagination, filtering, deletion, payment marking,
 * and KPI metrics.
 */
class InvoiceService
{
    /**
     * Get paginated invoice records with eager loaded relations.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param mixed $status Invoice status filter tokens.
     *
     * @return LengthAwarePaginator<int, Invoice>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        mixed $status = null,
    ): LengthAwarePaginator {
        return Invoice::query()
            ->with(['tenant', 'subscription.plan'])
            ->forTenant()
            ->search($search)
            ->filterStatus(QueryFilter::filterList($status))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find invoice by ID or fail with eager loaded relations.
     *
     * @param int $id Record identifier.
     *
     * @return Invoice
     */
    public function findOrFail(int $id): Invoice
    {
        return Invoice::query()
            ->with(['tenant', 'subscription.plan', 'payments', 'invoiceItems'])
            ->findOrFail($id);
    }

    /**
     * Create a new invoice.
     *
     * @param array<string, mixed> $data
     *
     * @return Invoice
     */
    public function create(array $data): Invoice
    {
        return Invoice::query()->create($data);
    }

    /**
     * Update invoice.
     *
     * @param Invoice $invoice The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     *
     * @return Invoice
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice->fresh(['tenant', 'subscription.plan']);
    }

    /**
     * Delete a single invoice.
     *
     * @param Invoice $invoice The model instance to delete.
     *
     * @return bool
     */
    public function delete(Invoice $invoice): bool
    {
        return $invoice->delete();
    }

    /**
     * Delete multiple invoices by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of deleted records.
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = Invoice::query()->whereIn('id', $ids)->get();
            $deleted = 0;

            foreach ($records as $record) {
                if ($record->delete()) {
                    $deleted++;
                }
            }

            return $deleted;
        });
    }

    /**
     * Filter by tenant.
     *
     * @param string $tenantId Tenant identifier.
     *
     * @return Collection<int, Invoice>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return Invoice::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param string $status Status value to filter by.
     *
     * @return Collection<int, Invoice>
     */
    public function getByStatus(string $status): Collection
    {
        return Invoice::query()->where('status', $status)->get();
    }

    /**
     * Filter by subscription.
     *
     * @param int $subscriptionId Subscription identifier.
     *
     * @return Collection<int, Invoice>
     */
    public function getBySubscription(int $subscriptionId): Collection
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
            ->with(['tenant', 'subscription.plan'])
            ->forTenant()
            ->where('status', InvoiceStatus::Open->value)
            ->where('due_date', '<', now())
            ->latest()
            ->get();
    }

    /**
     * Mark an invoice as paid.
     *
     * @param Invoice $invoice The invoice to mark as paid.
     * @param string|null $paymentIntentId Optional payment provider intent identifier.
     *
     * @return Invoice
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

        return $invoice->fresh(['tenant', 'subscription.plan']);
    }

    /**
     * Get invoices by tenant with items.
     *
     * @param string $tenantId Tenant identifier.
     *
     * @return Collection<int, Invoice>
     */
    public function getByTenantWithItems(string $tenantId): Collection
    {
        return Invoice::query()
            ->with(['invoiceItems', 'payments'])
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
