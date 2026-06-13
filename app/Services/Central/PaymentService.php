<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\PaymentStatus;
use App\Models\Central\Payment;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Central tenant payment transaction records and queries.
 *
 * Encapsulates all business logic for payment management, including
 * creation, updates, pagination, filtering, deletion, refunds,
 * and KPI metrics.
 */
class PaymentService
{
    /**
     * Get paginated payment records with eager loaded relations.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param mixed $status Payment status filter tokens.
     *
     * @return LengthAwarePaginator<int, Payment>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        mixed $status = null,
    ): LengthAwarePaginator {
        return Payment::query()
            ->with(['tenant', 'invoice'])
            ->forTenant()
            ->search($search)
            ->filterStatus(QueryFilter::filterList($status))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find payment by ID or fail with eager loaded relations.
     *
     * @param int $id Record identifier.
     *
     * @return Payment
     */
    public function findOrFail(int $id): Payment
    {
        return Payment::query()
            ->with(['tenant', 'invoice'])
            ->findOrFail($id);
    }

    /**
     * Create a new payment.
     *
     * @param array<string, mixed> $data
     *
     * @return Payment
     */
    public function create(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    /**
     * Update payment.
     *
     * @param Payment $payment The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     *
     * @return Payment
     */
    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->fresh(['tenant', 'invoice']);
    }

    /**
     * Delete a single payment.
     *
     * @param Payment $payment The model instance to delete.
     *
     * @return bool
     */
    public function delete(Payment $payment): bool
    {
        return $payment->delete();
    }

    /**
     * Delete multiple payments by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of deleted records.
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = Payment::query()->whereIn('id', $ids)->get();
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
     * @return Collection<int, Payment>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return Payment::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param string $status Status value to filter by.
     *
     * @return Collection<int, Payment>
     */
    public function getByStatus(string $status): Collection
    {
        return Payment::query()->where('status', $status)->get();
    }

    /**
     * Filter by invoice.
     *
     * @param int $invoiceId Invoice identifier.
     *
     * @return Collection<int, Payment>
     */
    public function getByInvoice(int $invoiceId): Collection
    {
        return Payment::query()->where('invoice_id', $invoiceId)->get();
    }

    /**
     * Process a payment refund.
     *
     * @param Payment $payment The payment to refund.
     * @param int|null $amount Refunded amount in smallest currency unit.
     *
     * @return Payment
     */
    public function refund(Payment $payment, ?int $amount = null): Payment
    {
        $refundAmount = $amount ?? $payment->amount;

        $payment->update([
            'status' => PaymentStatus::Refunded->value,
            'refunded_amount' => $refundAmount,
        ]);

        return $payment->fresh(['tenant', 'invoice']);
    }

    /**
     * Get payments by tenant with invoice.
     *
     * @param string $tenantId Tenant identifier.
     *
     * @return Collection<int, Payment>
     */
    public function getByTenantWithInvoice(string $tenantId): Collection
    {
        return Payment::query()
            ->with(['invoice'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * KPI card metrics for payments.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $query = Payment::query()->forTenant();

        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalCollected = (int) (clone $query)
            ->where('status', PaymentStatus::Succeeded->value)
            ->sum('amount');

        $totalRefunded = (int) (clone $query)->sum('refunded_amount');

        return [
            ['key' => 'total', 'label' => 'Total Payments', 'value' => (int) $counts->sum()],
            ['key' => 'succeeded', 'label' => 'Succeeded', 'value' => (int) ($counts[PaymentStatus::Succeeded->value] ?? 0)],
            ['key' => 'pending', 'label' => 'Pending', 'value' => (int) ($counts[PaymentStatus::Pending->value] ?? 0)],
            ['key' => 'failed', 'label' => 'Failed', 'value' => (int) ($counts[PaymentStatus::Failed->value] ?? 0)],
            ['key' => 'refunded', 'label' => 'Refunded', 'value' => (int) ($counts[PaymentStatus::Refunded->value] ?? 0)],
            ['key' => 'total_collected', 'label' => 'Total Collected', 'value' => $totalCollected],
            ['key' => 'total_refunded', 'label' => 'Total Refunded', 'value' => $totalRefunded],
        ];
    }
}
