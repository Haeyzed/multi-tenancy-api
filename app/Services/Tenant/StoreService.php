<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\GeneralSetting;
use App\Models\Tenant\Store;
use App\Models\Tenant\StoreSetting;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Tenant store records and queries.
 */
class StoreService
{
    use DeletesManyRecords;

    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'logoMedia',
        'faviconMedia',
        'settings',
        'primaryAddress',
        'addresses',
    ];

    /**
     * Default operational settings payload for a new store.
     *
     * @return array<string, mixed>
     */
    public function createDefaultSettingsPayload(Store $store): array
    {
        return $this->defaultStoreSettings($store);
    }

    /**
     * Default operational settings applied when a store is created.
     *
     * @return array<string, mixed>
     */
    private function defaultStoreSettings(Store $store): array
    {
        $general = GeneralSetting::query()->first();

        return [
            'currency' => $general?->default_currency ?? 'USD',
            'default_language' => $general?->default_language ?? 'en',
            'timezone' => $general?->default_timezone ?? 'UTC',
            'weight_unit' => $general?->default_weight_unit ?? 'kg',
            'dimension_unit' => $general?->default_dimension_unit ?? 'cm',
            'primary_color' => '#3B82F6',
            'secondary_color' => '#10B981',
            'order_number_prefix' => 'ORD-',
            'order_number_start' => 1000,
        ];
    }

    /**
     * Base query with store detail relations.
     *
     * @return Builder<Store>
     */
    private function queryWithDetails(): Builder
    {
        return Store::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Get paginated store records.
     *
     * @param  list<string>  $isActive
     * @return LengthAwarePaginator<int, Store>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $isActive = [],
        array $types = [],
    ): LengthAwarePaginator {
        return $this->queryWithDetails()
            ->search($search)
            ->filterIsActive($isActive)
            ->filterType($types)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * List active stores as value/label pairs for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public function getOptions(): array
    {
        return Store::query()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Store $store): array => [
                'value' => $store->id,
                'label' => $store->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Find store by ID or fail.
     */
    public function findOrFail(string $id): Store
    {
        return $this->queryWithDetails()->findOrFail($id);
    }

    /**
     * Create a new store with default settings.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Store
    {
        return DB::transaction(function () use ($data): Store {
            if (($data['is_primary'] ?? false) === true) {
                Store::query()->update(['is_primary' => false]);
            }

            $store = Store::query()->create($data);

            StoreSetting::query()->create(array_merge(
                ['store_id' => $store->id],
                $this->defaultStoreSettings($store),
            ));

            return $store->fresh(self::DETAIL_RELATIONS);
        });
    }

    /**
     * Update store.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Store $store, array $data): Store
    {
        return DB::transaction(function () use ($store, $data): Store {
            if (($data['is_primary'] ?? false) === true) {
                Store::query()
                    ->where('id', '!=', $store->id)
                    ->update(['is_primary' => false]);
            }

            $store->update($data);

            return $store->fresh(self::DETAIL_RELATIONS);
        });
    }

    /**
     * Delete store and its settings.
     */
    public function delete(Store $store): bool
    {
        if ($store->is_primary && Store::query()->where('id', '!=', $store->id)->exists()) {
            return false;
        }

        return (bool) $store->delete();
    }

    /**
     * Delete multiple stores by ID.
     *
     * @param  list<string>  $ids
     */
    public function deleteMany(array $ids): int
    {
        $deleted = 0;

        Store::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Store $store) use (&$deleted): void {
                if ($this->delete($store)) {
                    $deleted++;
                }
            });

        return $deleted;
    }

    /**
     * Toggle the store active flag.
     */
    public function toggleActive(Store $store): Store
    {
        $store->update(['is_active' => ! $store->is_active]);

        return $store->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Mark a store as the primary storefront.
     */
    public function setPrimary(Store $store): Store
    {
        return DB::transaction(function () use ($store): Store {
            Store::query()->update(['is_primary' => false]);
            $store->update(['is_primary' => true, 'is_active' => true]);

            return $store->fresh(self::DETAIL_RELATIONS);
        });
    }

    /**
     * KPI card metrics for stores.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = Store::query()->count();
        $active = Store::query()->where('is_active', true)->count();
        $primary = Store::query()->where('is_primary', true)->count();
        $withLogo = Store::query()->whereNotNull('logo_media_id')->count();

        return [
            ['key' => 'total', 'label' => 'Total Stores', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'primary', 'label' => 'Primary Store', 'value' => $primary],
            ['key' => 'with_logo', 'label' => 'With Logo', 'value' => $withLogo],
        ];
    }
}
