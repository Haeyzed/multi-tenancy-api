<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Brand;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Tenant product brand records and queries.
 */
class BrandService
{
    use DeletesManyRecords;

    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'logoMedia',
    ];

    /**
     * Get paginated brand records.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param list<string> $isActive Active/inactive filter tokens.
     * @return LengthAwarePaginator<int, Brand>
     */
    public function getPaginated(
        int     $perPage = 15,
        ?string $search = null,
        array   $isActive = [],
    ): LengthAwarePaginator
    {
        return $this->queryWithDetails()
            ->search($search)
            ->filterIsActive($isActive)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Base query with brand detail relations.
     *
     * @return Builder<Brand>
     */
    private function queryWithDetails(): Builder
    {
        return Brand::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Find brand by ID or fail.
     *
     * @param int $id Record identifier.
     */
    public function findOrFail(int $id): Brand
    {
        return $this->queryWithDetails()->findOrFail($id);
    }

    /**
     * Create a new brand.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Brand
    {
        return Brand::query()->create($data);
    }

    /**
     * Update brand.
     *
     * @param Brand $brand The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(Brand $brand, array $data): Brand
    {
        $brand->update($data);

        return $brand->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Delete brand.
     *
     * @param Brand $brand The model instance to delete.
     */
    public function delete(Brand $brand): bool
    {
        return $brand->delete();
    }

    /**
     * Delete multiple brands by ID.
     *
     * @param list<int> $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->deleteManyByIds(Brand::class, $ids);
    }

    /**
     * Get only active brands.
     *
     * @return Collection<int, Brand>
     */
    public function getActive(): Collection
    {
        return Brand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active brands as value/label pairs for select inputs.
     *
     * @return list<array{value: int, label: string}>
     */
    public function getOptions(): array
    {
        return Brand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn(Brand $brand): array => [
                'value' => $brand->id,
                'label' => $brand->name,
            ])
            ->values()
            ->all();
    }

    /**
     * KPI card metrics for brands.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = Brand::query()->count();
        $active = Brand::query()->where('is_active', true)->count();
        $inactive = Brand::query()->where('is_active', false)->count();
        $withLogo = Brand::query()->whereNotNull('logo_media_id')->count();

        return [
            ['key' => 'total', 'label' => 'Total Brands', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'inactive', 'label' => 'Inactive', 'value' => $inactive],
            ['key' => 'with_logo', 'label' => 'With Logo', 'value' => $withLogo],
        ];
    }
}
