<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteBrandsRequest;
use App\Http\Requests\Tenant\StoreBrandRequest;
use App\Http\Requests\Tenant\UpdateBrandRequest;
use App\Http\Resources\Tenant\BrandResource;
use App\Models\Tenant\Brand;
use App\Services\Tenant\BrandService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Product brands for the tenant catalog.
 */
class BrandController extends Controller
{
    public function __construct(
        private readonly BrandService $service,
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = QueryFilter::parseList($request->query('is_active'));

        $items = $this->service->getPaginated($perPage, $search, $isActive);

        return $this->paginated($items, BrandResource::collection($items), 'Brands retrieved successfully.');
    }

    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Brand options retrieved successfully.');
    }

    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Brand KPI metrics retrieved successfully.',
        );
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new BrandResource($item->load('logoMedia')), 'Brand created successfully.');
    }

    public function show(Brand $brand): JsonResponse
    {
        $item = $this->service->findOrFail($brand->id);

        return $this->success(new BrandResource($item), 'Brand retrieved successfully.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $item = $this->service->update($brand, $request->validated());

        return $this->updated(new BrandResource($item), 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $this->service->delete($brand);

        return $this->deleted('Brand deleted successfully.');
    }

    public function bulkDestroy(BulkDeleteBrandsRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} brand(s) deleted successfully.",
        );
    }
}
