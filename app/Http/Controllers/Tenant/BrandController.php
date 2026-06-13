<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreBrandRequest;
use App\Http\Requests\Tenant\UpdateBrandRequest;
use App\Http\Resources\Tenant\BrandResource;
use App\Models\Tenant\Brand;
use App\Services\Tenant\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Product brands for the tenant catalog.
 *
 * Acts as a thin traffic controller, delegating all business logic
 * to the BrandService layer.
 */
class BrandController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param BrandService $service
     */
    public function __construct(
        private readonly BrandService $service,
    ) {}

    /**
     * Get paginated brand records.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $isActive = $request->query('is_active');
        $trashed = $request->query('trashed');

        $items = $this->service->getPaginated($perPage, $search, $isActive, $trashed);

        return $this->paginated($items, BrandResource::collection($items), 'Brands retrieved successfully.');
    }

    /**
     * List active brands as value/label pairs for select inputs.
     *
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Brand options retrieved successfully.');
    }

    /**
     * KPI card metrics for brands.
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Brand KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new brand.
     *
     * @param StoreBrandRequest $request Validated request payload.
     *
     * @return JsonResponse
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(
            new BrandResource($item->load('logoMedia')),
            'Brand created successfully.',
        );
    }

    /**
     * Find brand by route binding.
     *
     * @param Brand $brand Brand instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function show(Brand $brand): JsonResponse
    {
        $item = $this->service->findOrFail($brand->id);

        return $this->success(new BrandResource($item), 'Brand retrieved successfully.');
    }

    /**
     * Update brand.
     *
     * @param UpdateBrandRequest $request Validated request payload.
     * @param Brand $brand Brand instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $item = $this->service->update($brand, $request->validated());

        return $this->updated(
            new BrandResource($item),
            'Brand updated successfully.',
        );
    }

    /**
     * Delete brand.
     *
     * @param Brand $brand Brand instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function destroy(Brand $brand): JsonResponse
    {
        $this->service->delete($brand);

        return $this->deleted('Brand deleted successfully.');
    }

    /**
     * Delete multiple brands in one request.
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
            "{$deleted} brand(s) deleted successfully.",
        );
    }

    /**
     * Restore a soft-deleted brand.
     *
     * @param int $id Trashed record identifier.
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $item = $this->service->restore($id);

        return $this->success(
            new BrandResource($item),
            'Brand restored successfully.',
        );
    }

    /**
     * Restore multiple soft-deleted brands.
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
            "{$restored} brand(s) restored successfully.",
        );
    }

    /**
     * Unlink all products from a brand.
     *
     * @param Brand $brand Brand instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function unlink(Brand $brand): JsonResponse
    {
        $unlinked = $this->service->unlinkProducts($brand);

        return $this->success(
            ['unlinked' => $unlinked],
            "{$unlinked} product(s) unlinked from brand successfully.",
        );
    }

    /**
     * Unlink all products from multiple brands.
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
            "{$unlinked} product(s) unlinked from selected brand(s) successfully.",
        );
    }

    /**
     * Toggle the active status of a brand.
     *
     * @param Brand $brand Brand instance resolved via route model binding.
     *
     * @return JsonResponse
     */
    public function toggleActive(Brand $brand): JsonResponse
    {
        $item = $this->service->toggleActive($brand);

        return $this->success(
            new BrandResource($item),
            'Brand active status toggled successfully.',
        );
    }
}
