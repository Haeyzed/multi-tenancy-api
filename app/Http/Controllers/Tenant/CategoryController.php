<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteCategoriesRequest;
use App\Http\Requests\Tenant\StoreCategoryRequest;
use App\Http\Requests\Tenant\UpdateCategoryRequest;
use App\Http\Resources\Tenant\CategoryResource;
use App\Models\Tenant\Category;
use App\Services\Tenant\CategoryService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Product categories for the tenant catalog.
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $service,
    )
    {
    }

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

    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Category options retrieved successfully.');
    }

    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Category KPI metrics retrieved successfully.',
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(
            new CategoryResource($item->load(['parent', 'bannerMedia', 'iconMedia'])),
            'Category created successfully.',
        );
    }

    public function show(Category $category): JsonResponse
    {
        $item = $this->service->findOrFail($category->id);

        return $this->success(new CategoryResource($item), 'Category retrieved successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $item = $this->service->update($category, $request->validated());

        return $this->updated(new CategoryResource($item), 'Category updated successfully.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->service->delete($category);

        return $this->deleted('Category deleted successfully.');
    }

    public function bulkDestroy(BulkDeleteCategoriesRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} categor(ies) deleted successfully.",
        );
    }
}
