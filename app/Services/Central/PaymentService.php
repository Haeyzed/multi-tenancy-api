<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Payment records and queries.
 */
class PaymentService
{
    /**
     * Get all Payment records.
     *
     * @return Collection<int, Payment>
     */
    public function getAll(): Collection
    {
        return Payment::query()->get();
    }

    /**
     * Get paginated Payment records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, Payment>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Payment::query()->paginate($perPage);
    }

    /**
     * Find Payment by ID.
     *
     * @param  string  $id  Record identifier.
     */
    public function find(string $id): ?Payment
    {
        return Payment::query()->find($id);
    }

    /**
     * Find Payment by ID or fail.
     *
     * @param  string  $id  Record identifier.
     */
    public function findOrFail(string $id): Payment
    {
        return Payment::query()->findOrFail($id);
    }

    /**
     * Create a new Payment.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    /**
     * Update Payment.
     *
     * @param  Payment  $payment  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Payment $payment, array $data): Payment
    {
        $payment->query()->update($data);

        return $payment->fresh();
    }

    /**
     * Delete Payment.
     *
     * @param  Payment  $payment  The model instance to delete.
     */
    public function delete(Payment $payment): bool
    {
        return $payment->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, Payment>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return Payment::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param  string  $status  Status value to filter by.
     * @return Collection<int, Payment>
     */
    public function getByStatus(string $status): Collection
    {
        return Payment::query()->where('status', $status)->get();
    }

    /**
     * Filter by invoice.
     *
     * @param  string  $invoiceId  Invoice UUID to filter by.
     * @return Collection<int, Payment>
     */
    public function getByInvoice(string $invoiceId): Collection
    {
        return Payment::query()->where('invoice_id', $invoiceId)->get();
    }

    /**
     * Process a payment refund.
     *
     * @param  Payment  $payment  The payment to refund.
     * @param  int  $amount  Refunded amount in smallest currency unit.
     */
    public function refund(Payment $payment, int $amount): Payment
    {
        $payment->query()->update([
            'status' => 'refunded',
            'refunded_amount' => $amount,
        ]);

        return $payment->fresh();
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
}
