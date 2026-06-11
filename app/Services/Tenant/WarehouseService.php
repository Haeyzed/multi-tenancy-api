<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Warehouse;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Tenant warehouse records and queries.
 */
class WarehouseService
{
    use DeletesManyRecords;

    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'store',
    ];

    /**
     * Get paginated warehouse records.
     *
     * @param  list<string>  $isActive
     * @return LengthAwarePaginator<int, Warehouse>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $isActive = [],
        ?string $storeId = null,
    ): LengthAwarePaginator {
        return $this->queryWithDetails()
            ->search($search)
            ->filterIsActive($isActive)
            ->when($storeId, fn (Builder $q) => $q->where('store_id', $storeId))
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Base query with warehouse detail relations.
     *
     * @return Builder<Warehouse>
     */
    private function queryWithDetails(): Builder
    {
        return Warehouse::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Find warehouse by ID or fail.
     */
    public function findOrFail(string $id): Warehouse
    {
        return $this->queryWithDetails()->findOrFail($id);
    }

    /**
     * Create a new warehouse.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Warehouse
    {
        return Warehouse::query()->create($data);
    }

    /**
     * Update warehouse.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        return $warehouse->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Delete warehouse.
     */
    public function delete(Warehouse $warehouse): bool
    {
        return (bool) $warehouse->delete();
    }

    /**
     * Delete multiple warehouses by ID.
     *
     * @param  list<string>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->deleteManyByIds(Warehouse::class, $ids);
    }

    /**
     * Toggle the warehouse active flag.
     */
    public function toggleActive(Warehouse $warehouse): Warehouse
    {
        $warehouse->update(['is_active' => ! $warehouse->is_active]);

        return $warehouse->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * List active warehouses as value/label pairs for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public function getOptions(?string $storeId = null): array
    {
        return Warehouse::query()
            ->where('is_active', true)
            ->when($storeId, fn (Builder $q) => $q->where('store_id', $storeId))
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Warehouse $warehouse): array => [
                'value' => $warehouse->id,
                'label' => $warehouse->name,
            ])
            ->values()
            ->all();
    }

    /**
     * KPI card metrics for warehouses.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = Warehouse::query()->count();
        $active = Warehouse::query()->where('is_active', true)->count();
        $linked = Warehouse::query()->whereNotNull('store_id')->count();

        return [
            ['key' => 'total', 'label' => 'Total Warehouses', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'linked_to_store', 'label' => 'Linked to Store', 'value' => $linked],
        ];
    }
}
