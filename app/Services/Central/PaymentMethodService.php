<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central PaymentMethod records and queries.
 */
class PaymentMethodService
{
    /**
     * Get all PaymentMethod records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, PaymentMethod>
     */
    public function getAll(?string $search = null): Collection
    {
        return PaymentMethod::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated PaymentMethod records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, PaymentMethod>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return PaymentMethod::query()
            ->forTenant()
            ->search($search)
            ->paginate($perPage);
    }

    /**
     * Find PaymentMethod by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?PaymentMethod
    {
        return PaymentMethod::query()->find($id);
    }

    /**
     * Find PaymentMethod by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): PaymentMethod
    {
        return PaymentMethod::query()->findOrFail($id);
    }

    /**
     * Create a new PaymentMethod.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PaymentMethod
    {
        return PaymentMethod::query()->create($data);
    }

    /**
     * Update PaymentMethod.
     *
     * @param  PaymentMethod  $paymentMethod  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $paymentMethod->query()->update($data);

        return $paymentMethod->fresh();
    }

    /**
     * Delete PaymentMethod.
     *
     * @param  PaymentMethod  $paymentMethod  The model instance to delete.
     */
    public function delete(PaymentMethod $paymentMethod): bool
    {
        return $paymentMethod->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, PaymentMethod>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return PaymentMethod::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Set the payment method as the tenant default.
     *
     * @param  PaymentMethod  $paymentMethod  The payment method to set as default.
     */
    public function setDefault(PaymentMethod $paymentMethod): PaymentMethod
    {
        PaymentMethod::query()->where('tenant_id', $paymentMethod->tenant_id)
            ->where('id', '!=', $paymentMethod->id)
            ->update(['is_default' => false]);
        $paymentMethod->query()->update(['is_default' => true]);

        return $paymentMethod->fresh();
    }
}
