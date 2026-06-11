<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\PaymentStatus;
use App\Models\Central\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Payment records and queries.
 */
class PaymentService
{
    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'tenant',
        'invoice',
    ];

    /**
     * Get all Payment records.
     *
     * @param string|null $search Optional search term.
     * @return Collection<int, Payment>
     */
    public function getAll(?string $search = null): Collection
    {
        return Payment::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated Payment records.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @return LengthAwarePaginator<int, Payment>
     */
    /**
     * @param list<string> $status
     */
    public function getPaginated(
        int     $perPage = 15,
        ?string $search = null,
        array   $status = [],
    ): LengthAwarePaginator
    {
        return Payment::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterStatus($status)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find Payment by ID.
     *
     * @param string $id Record identifier.
     */
    public function find(string $id): ?Payment
    {
        return Payment::query()->find($id);
    }

    /**
     * Find Payment by ID or fail.
     *
     * @param string $id Record identifier.
     */
    public function findOrFail(string $id): Payment
    {
        return Payment::query()
            ->with(self::LIST_RELATIONS)
            ->findOrFail($id);
    }

    /**
     * Create a new Payment.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    /**
     * Delete Payment.
     *
     * @param Payment $payment The model instance to delete.
     */
    public function delete(Payment $payment): bool
    {
        return (bool)$payment->delete();
    }

    /**
     * Filter by tenant.
     *
     * @param string $tenantId Tenant UUID.
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
     * @return Collection<int, Payment>
     */
    public function getByStatus(string $status): Collection
    {
        return Payment::query()->where('status', $status)->get();
    }

    /**
     * Filter by invoice.
     *
     * @param string $invoiceId Invoice UUID to filter by.
     * @return Collection<int, Payment>
     */
    public function getByInvoice(string $invoiceId): Collection
    {
        return Payment::query()->where('invoice_id', $invoiceId)->get();
    }

    /**
     * Process a payment refund.
     *
     * @param Payment $payment The payment to refund.
     * @param int $amount Refunded amount in smallest currency unit.
     */
    public function refund(Payment $payment, ?int $amount = null): Payment
    {
        $refundAmount = $amount ?? $payment->amount;

        $payment->update([
            'status' => PaymentStatus::Refunded->value,
            'refunded_amount' => $refundAmount,
        ]);

        return $payment->fresh(self::LIST_RELATIONS);
    }

    /**
     * Update Payment.
     *
     * @param Payment $payment The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->fresh(self::LIST_RELATIONS);
    }

    /**
     * Get payments by tenant with invoice.
     *
     * @return Collection<int, Payment>
     */
    public function getByTenantWithInvoice(string $tenantId): Collection
    {
        return Payment::query()->with('invoice')
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

        $totalCollected = (int)(clone $query)
            ->where('status', PaymentStatus::Succeeded->value)
            ->sum('amount');

        $totalRefunded = (int)(clone $query)->sum('refunded_amount');

        return [
            ['key' => 'total', 'label' => 'Total Payments', 'value' => (int)$counts->sum()],
            ['key' => 'succeeded', 'label' => 'Succeeded', 'value' => (int)($counts[PaymentStatus::Succeeded->value] ?? 0)],
            ['key' => 'pending', 'label' => 'Pending', 'value' => (int)($counts[PaymentStatus::Pending->value] ?? 0)],
            ['key' => 'failed', 'label' => 'Failed', 'value' => (int)($counts[PaymentStatus::Failed->value] ?? 0)],
            ['key' => 'refunded', 'label' => 'Refunded', 'value' => (int)($counts[PaymentStatus::Refunded->value] ?? 0)],
            ['key' => 'total_collected', 'label' => 'Total Collected', 'value' => $totalCollected],
            ['key' => 'total_refunded', 'label' => 'Total Refunded', 'value' => $totalRefunded],
        ];
    }
}
