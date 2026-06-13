<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCategoryRequest;
use App\Http\Requests\Tenant\UpdateCategoryRequest;
use App\Http\Resources\Tenant\CategoryResource;
use App\Models\Tenant\Category;
use App\Services\Tenant\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\QueryFilter;

/**
 * Product categories for the tenant catalog.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the CategoryService layer.
 */
class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param CategoryService $service
     */
    public function __construct(
        private readonly CategoryService $service,
    ) {}

    /**
     * Get paginated category records.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = QueryFilter::parseList($request->query('is_active'));
        $isFeatured = QueryFilter::parseList($request->query('is_featured'));
        $showInMenu = QueryFilter::parseList($request->query('show_in_menu'));

        $items = $this->service->getPaginated($perPage, $search, $isActive, $isFeatured, $showInMenu);

        return $this->paginated($items, CategoryResource::collection($items), 'Categories retrieved successfully.');
    }

    /**
     * List active categories as value/label pairs for select inputs.
     *
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Category options retrieved successfully.');
    }

    /**
     * KPI card metrics for categories.
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Category KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new category.
     *
     * @param StoreCategoryRequest $request Validated request payload.
     *
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(
            new CategoryResource($item->load(['parent', 'bannerMedia', 'iconMedia'])),
            'Category created successfully.',
        );
    }

    /**
     * Find category by route binding.
     *
     * @param Category $category Category instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        $item = $this->service->findOrFail($category->id);

        return $this->success(new CategoryResource($item), 'Category retrieved successfully.');
    }

    /**
     * Update category.
     *
     * @param UpdateCategoryRequest $request Validated request payload.
     * @param Category $category Category instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $item = $this->service->update($category, $request->validated());

        return $this->updated(
            new CategoryResource($item),
            'Category updated successfully.',
        );
    }

    /**
     * Delete category.
     *
     * @param Category $category Category instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->service->delete($category);

        return $this->deleted('Category deleted successfully.');
    }

    /**
     * Delete multiple categories in one request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $deleted = $this->service->deleteMany($ids);

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} categor(ies) deleted successfully.",
        );
    }

    /**
     * Restore a soft-deleted category.
     *
     * @param int $id Trashed record identifier.
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $item = $this->service->restore($id);

        return $this->success(
            new CategoryResource($item),
            'Category restored successfully.',
        );
    }

    /**
     * Restore multiple soft-deleted categories.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $restored = $this->service->restoreMany($ids);

        return $this->success(
            ['restored' => $restored],
            "{$restored} categor(ies) restored successfully.",
        );
    }

    /**
     * Unlink all products from a category.
     *
     * @param Category $category Category instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function unlink(Category $category): JsonResponse
    {
        $unlinked = $this->service->unlinkProducts($category);

        return $this->success(
            ['unlinked' => $unlinked],
            "{$unlinked} product(s) unlinked from category successfully.",
        );
    }

    /**
     * Unlink all products from multiple categories.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkUnlink(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $unlinked = $this->service->bulkUnlinkProducts($ids);

        return $this->success(
            ['unlinked' => $unlinked],
            "{$unlinked} product(s) unlinked from selected categor(ies) successfully.",
        );
    }

    /**
     * Toggle the active status of a category.
     *
     * @param Category $category Category instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function toggleActive(Category $category): JsonResponse
    {
        $item = $this->service->toggleActive($category);

        return $this->success(
            new CategoryResource($item),
            'Category active status toggled successfully.',
        );
    }

    /**
     * Toggle the featured status of a category.
     *
     * @param Category $category Category instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function toggleFeatured(Category $category): JsonResponse
    {
        $item = $this->service->toggleFeatured($category);

        return $this->success(
            new CategoryResource($item),
            'Category featured status toggled successfully.',
        );
    }

    /**
     * Toggle the menu visibility of a category.
     *
     * @param Category $category Category instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function toggleShowInMenu(Category $category): JsonResponse
    {
        $item = $this->service->toggleShowInMenu($category);

        return $this->success(
            new CategoryResource($item),
            'Category menu visibility toggled successfully.',
        );
    }
}
