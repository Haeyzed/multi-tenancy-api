<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BulkDeleteProductsRequest;
use App\Http\Requests\Tenant\StoreProductRequest;
use App\Http\Requests\Tenant\UpdateProductRequest;
use App\Http\Resources\Tenant\ProductResource;
use App\Models\Tenant\Product;
use App\Services\Tenant\ProductService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Products for the tenant catalog.
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service,
    ) {}

    /**
     * Get paginated product records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $statuses = QueryFilter::parseList($request->query('status'));

        $items = $this->service->getPaginated($perPage, $search, $statuses);

        return $this->paginated($items, ProductResource::collection($items), 'Products retrieved successfully.');
    }

    /**
     * List active products as value/label pairs for select inputs.
     */
    public function options(): JsonResponse
    {
        return $this->success($this->service->getOptions(), 'Product options retrieved successfully.');
    }

    /**
     * KPI card metrics for products.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Product KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new product.
     *
     * @param  StoreProductRequest  $request  Validated request payload.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(
            new ProductResource($item->load(['brand', 'category', 'createdBy', 'updatedBy'])),
            'Product created successfully.',
        );
    }

    /**
     * Find product by route binding.
     *
     * @param  Product  $product  Product instance.
     */
    public function show(Product $product): JsonResponse
    {
        $item = $this->service->findOrFail($product->id);

        return $this->success(new ProductResource($item), 'Product retrieved successfully.');
    }

    /**
     * Update product.
     *
     * @param  UpdateProductRequest  $request  Validated request payload.
     * @param  Product  $product  Product instance.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $item = $this->service->update($product, $request->validated());

        return $this->updated(new ProductResource($item), 'Product updated successfully.');
    }

    /**
     * Delete product.
     *
     * @param  Product  $product  Product instance.
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->service->delete($product);

        return $this->deleted('Product deleted successfully.');
    }

    /**
     * Delete multiple products in one request.
     */
    public function bulkDestroy(BulkDeleteProductsRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} product(s) deleted successfully.",
        );
    }
}
