<?php

declare(strict_types=1);

namespace App\Services\Concerns;

use App\Models\Tenant\Brand;
use App\Models\Tenant\Category;
use App\Models\Tenant\CategoryProduct;
use App\Models\Tenant\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Guards and helpers for brand/category links to catalog products.
 */
trait GuardsCatalogProductLinks
{
    /**
     * Count products linked to a category via column or pivot.
     */
    protected function countCategoryLinkedProducts(Category $category): int
    {
        $productIds = Product::query()
            ->where('category_id', $category->id)
            ->pluck('id');

        $pivotProductIds = CategoryProduct::query()
            ->where('category_id', $category->id)
            ->pluck('product_id');

        return $productIds->merge($pivotProductIds)->unique()->count();
    }

    /**
     * Ensure a brand is not linked to any products before deletion.
     */
    protected function assertBrandNotLinkedToProducts(Brand $brand): void
    {
        $count = $brand->products()->count();

        if ($count === 0) {
            return;
        }

        throw ValidationException::withMessages([
            'brand' => [
                "Cannot delete \"{$brand->name}\" because it is linked to {$count} product(s). Unlink products first.",
            ],
        ]);
    }

    /**
     * Ensure none of the given brands are linked to products.
     *
     * @param list<int> $ids
     */
    protected function assertBrandsNotLinkedToProducts(array $ids): void
    {
        /** @var Collection<int, Brand> $brands */
        $brands = Brand::query()
            ->whereIn('id', $ids)
            ->withCount('products')
            ->get();

        $linked = $brands->filter(static fn (Brand $brand): bool => $brand->products_count > 0);

        if ($linked->isEmpty()) {
            return;
        }

        $details = $linked
            ->map(static fn (Brand $brand): string => "\"{$brand->name}\" ({$brand->products_count})")
            ->implode(', ');

        throw ValidationException::withMessages([
            'ids' => [
                "Cannot delete brands linked to products: {$details}. Unlink products first.",
            ],
        ]);
    }

    /**
     * Ensure a category is not linked to any products before deletion.
     */
    protected function assertCategoryNotLinkedToProducts(Category $category): void
    {
        $count = $this->countCategoryLinkedProducts($category);

        if ($count === 0) {
            return;
        }

        throw ValidationException::withMessages([
            'category' => [
                "Cannot delete \"{$category->name}\" because it is linked to {$count} product(s). Unlink products first.",
            ],
        ]);
    }

    /**
     * Ensure none of the given categories are linked to products.
     *
     * @param list<string> $ids
     */
    protected function assertCategoriesNotLinkedToProducts(array $ids): void
    {
        /** @var Collection<int, Category> $categories */
        $categories = Category::query()->whereIn('id', $ids)->get();

        $linked = $categories
            ->map(fn (Category $category): array => [
                'category' => $category,
                'count' => $this->countCategoryLinkedProducts($category),
            ])
            ->filter(static fn (array $item): bool => $item['count'] > 0);

        if ($linked->isEmpty()) {
            return;
        }

        $details = $linked
            ->map(static fn (array $item): string => "\"{$item['category']->name}\" ({$item['count']})")
            ->implode(', ');

        throw ValidationException::withMessages([
            'ids' => [
                "Cannot delete categories linked to products: {$details}. Unlink products first.",
            ],
        ]);
    }

    /**
     * Remove all product links for a category.
     */
    protected function unlinkCategoryProducts(Category $category): int
    {
        $count = $this->countCategoryLinkedProducts($category);

        Product::query()
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        CategoryProduct::query()
            ->where('category_id', $category->id)
            ->delete();

        return $count;
    }
}
