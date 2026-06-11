<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Store;
use App\Models\Tenant\StoreAddress;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Store address records scoped to a store.
 */
class StoreAddressService
{
    use DeletesManyRecords;

    /**
     * Get paginated addresses for a store.
     *
     * @return LengthAwarePaginator<int, StoreAddress>
     */
    public function getPaginatedForStore(
        Store $store,
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {
        return StoreAddress::query()
            ->where('store_id', $store->id)
            ->search($search)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Find address belonging to a store or fail.
     */
    public function findForStoreOrFail(Store $store, int $id): StoreAddress
    {
        return StoreAddress::query()
            ->where('store_id', $store->id)
            ->findOrFail($id);
    }

    /**
     * Create an address for a store.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Store $store, array $data): StoreAddress
    {
        return DB::transaction(function () use ($store, $data): StoreAddress {
            $data['store_id'] = $store->id;

            if (($data['is_default'] ?? false) === true) {
                StoreAddress::query()
                    ->where('store_id', $store->id)
                    ->update(['is_default' => false]);
            }

            return StoreAddress::query()->create($data);
        });
    }

    /**
     * Update a store address.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(StoreAddress $address, array $data): StoreAddress
    {
        return DB::transaction(function () use ($address, $data): StoreAddress {
            if (($data['is_default'] ?? false) === true) {
                StoreAddress::query()
                    ->where('store_id', $address->store_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update($data);

            return $address->fresh();
        });
    }

    /**
     * Delete a store address.
     */
    public function delete(StoreAddress $address): bool
    {
        return (bool) $address->delete();
    }

    /**
     * Delete multiple addresses for a store.
     *
     * @param  list<int>  $ids
     */
    public function deleteManyForStore(Store $store, array $ids): int
    {
        return StoreAddress::query()
            ->where('store_id', $store->id)
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Mark an address as the default for its store.
     */
    public function setDefault(StoreAddress $address): StoreAddress
    {
        return DB::transaction(function () use ($address): StoreAddress {
            StoreAddress::query()
                ->where('store_id', $address->store_id)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);

            return $address->fresh();
        });
    }
}
