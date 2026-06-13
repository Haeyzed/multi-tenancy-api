<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Product;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Tenant product catalog records and queries.
 */
class ProductService
{
    use DeletesManyRecords;

    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'brand',
        'category',
        'createdBy',
        'updatedBy',
    ];

    /**
     * Base query with product detail relations.
     *
     * @return Builder<Product>
     */
    private function queryWithDetails(): Builder
    {
        return Product::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Get paginated product records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @param  list<string>  $statuses  Product status filter values.
     * @return LengthAwarePaginator<int, Product>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $statuses = [],
    ): LengthAwarePaginator {
        return $this->queryWithDetails()
            ->search($search)
            ->filterStatus($statuses)
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Find product by ID or fail.
     *
     * @param  string  $id  Record identifier.
     */
    public function findOrFail(int $id): Product
    {
        return $this->queryWithDetails()->findOrFail($id);
    }

    /**
     * Create a new product.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $userId = Auth::id();

        if ($userId !== null) {
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;
        }

        if (($data['status'] ?? 'draft') === 'active' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return Product::query()->create($data);
    }

    /**
     * Update product.
     *
     * @param  Product  $product  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Product $product, array $data): Product
    {
        $userId = Auth::id();

        if ($userId !== null) {
            $data['updated_by'] = $userId;
        }

        if (array_key_exists('status', $data) && $data['status'] === 'active' && $product->published_at === null) {
            $data['published_at'] = $data['published_at'] ?? now();
        }

        $product->update($data);

        return $product->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Delete product.
     *
     * @param  Product  $product  The model instance to delete.
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Delete multiple products by ID.
     *
     * @param  list<string>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->deleteManyByIds(Product::class, $ids);
    }

    /**
     * Restore soft-deleted product.
     *
     * @param  string  $id  Trashed record identifier.
     */
    public function restore(int $id): Product
    {
        $model = Product::withTrashed()->findOrFail($id);
        $model->restore();

        return $model->load(self::DETAIL_RELATIONS);
    }

    /**
     * Force delete product.
     *
     * @param  string  $id  Trashed record identifier.
     */
    public function forceDelete(int $id): bool
    {
        $model = Product::withTrashed()->findOrFail($id);

        return $model->forceDelete();
    }

    /**
     * Get only active products.
     *
     * @return Collection<int, Product>
     */
    public function getActive(): Collection
    {
        return Product::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active products as value/label pairs for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public function getOptions(): array
    {
        return Product::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'value' => $product->id,
                'label' => (string) $product->name,
            ])
            ->values()
            ->all();
    }

    /**
     * KPI card metrics for products.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = Product::query()->count();
        $active = Product::query()->where('status', 'active')->count();
        $draft = Product::query()->where('status', 'draft')->count();
        $featured = Product::query()->where('is_featured', true)->count();
        $withBrand = Product::query()->whereNotNull('brand_id')->count();
        $withCategory = Product::query()->whereNotNull('category_id')->count();

        return [
            ['key' => 'total', 'label' => 'Total Products', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'draft', 'label' => 'Draft', 'value' => $draft],
            ['key' => 'featured', 'label' => 'Featured', 'value' => $featured],
            ['key' => 'with_brand', 'label' => 'With Brand', 'value' => $withBrand],
            ['key' => 'with_category', 'label' => 'With Category', 'value' => $withCategory],
        ];
    }
}
