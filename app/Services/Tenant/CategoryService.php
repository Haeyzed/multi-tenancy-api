<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Category;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * Tenant product category records and queries.
 */
class CategoryService
{
    use DeletesManyRecords;

    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'parent',
        'bannerMedia',
        'iconMedia',
    ];

    /**
     * Get paginated category records.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param list<string> $isActive Active/inactive filter tokens.
     * @param list<string> $isFeatured Featured/unfeatured filter tokens.
     * @param list<string> $showInMenu Menu visibility filter tokens.
     * @return LengthAwarePaginator<int, Category>
     */
    public function getPaginated(
        int     $perPage = 15,
        ?string $search = null,
        array   $isActive = [],
        array   $isFeatured = [],
        array   $showInMenu = [],
    ): LengthAwarePaginator
    {
        return $this->queryWithDetails()
            ->search($search)
            ->filterIsActive($isActive)
            ->filterIsFeatured($isFeatured)
            ->filterShowInMenu($showInMenu)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Base query with category detail relations.
     *
     * @return Builder<Category>
     */
    private function queryWithDetails(): Builder
    {
        return Category::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Create a new category.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Category
    {
        $this->applyTreeFields($data);

        return Category::query()->create($data);
    }

    /**
     * Compute tree depth and path from parent assignment.
     *
     * @param array<string, mixed> $data
     */
    private function applyTreeFields(array &$data, ?Category $existing = null): void
    {
        if (!array_key_exists('parent_id', $data) && $existing === null) {
            $data['depth'] = 0;
            $data['path'] = $data['slug'] ?? null;

            return;
        }

        if (!array_key_exists('parent_id', $data) && $existing !== null) {
            if (array_key_exists('slug', $data) && $existing->parent_id === null) {
                $data['path'] = $data['slug'];
            }

            return;
        }

        $parentId = $data['parent_id'] ?? null;
        $slug = $data['slug'] ?? $existing?->slug ?? '';

        if ($parentId === null) {
            $data['depth'] = 0;
            $data['path'] = $slug;

            return;
        }

        $parent = Category::query()->findOrFail($parentId);
        $data['depth'] = $parent->depth + 1;
        $data['path'] = $parent->path !== null && $parent->path !== ''
            ? $parent->path . '/' . $slug
            : $slug;
    }

    /**
     * Find category by ID or fail.
     *
     * @param string $id Record identifier.
     */
    public function findOrFail(string $id): Category
    {
        return $this->queryWithDetails()->findOrFail($id);
    }

    /**
     * Update category.
     *
     * @param Category $category The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(Category $category, array $data): Category
    {
        if (array_key_exists('parent_id', $data) && $data['parent_id'] === $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => ['A category cannot be its own parent.'],
            ]);
        }

        $this->applyTreeFields($data, $category);
        $category->update($data);

        return $category->fresh(self::DETAIL_RELATIONS);
    }

    /**
     * Delete category.
     *
     * @param Category $category The model instance to delete.
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * Delete multiple categories by ID.
     *
     * @param list<string> $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->deleteManyByIds(Category::class, $ids);
    }

    /**
     * Restore soft-deleted category.
     *
     * @param string $id Trashed record identifier.
     */
    public function restore(string $id): Category
    {
        $model = Category::withTrashed()->findOrFail($id);
        $model->restore();

        return $model->load(self::DETAIL_RELATIONS);
    }

    /**
     * Force delete category.
     *
     * @param string $id Trashed record identifier.
     */
    public function forceDelete(string $id): bool
    {
        $model = Category::withTrashed()->findOrFail($id);

        return $model->forceDelete();
    }

    /**
     * Get only active categories.
     *
     * @return Collection<int, Category>
     */
    public function getActive(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active categories as value/label pairs for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public function getOptions(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn(Category $category): array => [
                'value' => $category->id,
                'label' => $category->name,
            ])
            ->values()
            ->all();
    }

    /**
     * KPI card metrics for categories.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = Category::query()->count();
        $active = Category::query()->where('is_active', true)->count();
        $featured = Category::query()->where('is_featured', true)->count();
        $inMenu = Category::query()->where('show_in_menu', true)->count();

        return [
            ['key' => 'total', 'label' => 'Total Categories', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'featured', 'label' => 'Featured', 'value' => $featured],
            ['key' => 'in_menu', 'label' => 'In Menu', 'value' => $inMenu],
        ];
    }
}
