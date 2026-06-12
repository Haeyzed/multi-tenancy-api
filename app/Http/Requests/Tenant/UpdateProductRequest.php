<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;

/**
 * Validates incoming data for updating a catalog product.
 */
class UpdateProductRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var \App\Models\Tenant\Product|null $product */
        $product = $this->route('product');

        return [
            /**
             * Display name of the product; optional on update.
             *
             * @var string $name
             */
            'name' => 'sometimes|string|max:255',

            /**
             * URL-friendly unique identifier; optional on update.
             *
             * @var string $slug
             */
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],

            /**
             * Stock keeping unit; optional on update.
             *
             * @var string $sku
             */
            'sku' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],

            /**
             * Barcode value; nullable.
             *
             * @var string|null $barcode
             */
            'barcode' => 'nullable|string|max:255',

            /**
             * Full product description; nullable.
             *
             * @var string|null $description
             */
            'description' => 'nullable|string',

            /**
             * Short summary for listings; nullable.
             *
             * @var string|null $short_description
             */
            'short_description' => 'nullable|string|max:500',

            /**
             * Brand ID this product belongs to; nullable.
             *
             * @var int|null $brand_id
             */
            'brand_id' => 'nullable|integer|exists:brands,id',

            /**
             * Primary category ID; nullable.
             *
             * @var string|null $category_id
             */
            'category_id' => 'nullable|uuid|exists:categories,id',

            /**
             * Product type; optional on update.
             *
             * @var string $type
             */
            'type' => ['sometimes', Rule::in(['simple', 'variable', 'digital', 'bundle', 'service'])],

            /**
             * Lifecycle status; optional on update.
             *
             * @var string $status
             */
            'status' => ['sometimes', Rule::in(['draft', 'active', 'archived', 'discontinued'])],

            /**
             * Storefront visibility; optional on update.
             *
             * @var string $visibility
             */
            'visibility' => ['sometimes', Rule::in(['visible', 'catalog', 'search', 'hidden'])],

            /**
             * Selling price; optional on update.
             *
             * @var numeric-string $price
             */
            'price' => 'sometimes|numeric|min:0',

            /**
             * Compare-at price; nullable.
             *
             * @var numeric-string|null $compare_price
             */
            'compare_price' => 'nullable|numeric|min:0',

            /**
             * Cost price; nullable.
             *
             * @var numeric-string|null $cost_price
             */
            'cost_price' => 'nullable|numeric|min:0',

            /**
             * Whether the product requires shipping; optional.
             *
             * @var bool $requires_shipping
             */
            'requires_shipping' => 'sometimes|boolean',

            /**
             * Whether the product is featured; optional.
             *
             * @var bool $is_featured
             */
            'is_featured' => 'sometimes|boolean',

            /**
             * Whether the product is a gift card; optional.
             *
             * @var bool $is_gift_card
             */
            'is_gift_card' => 'sometimes|boolean',

            /**
             * Whether backorders are allowed; optional.
             *
             * @var bool $allow_backorders
             */
            'allow_backorders' => 'sometimes|boolean',

            /**
             * Low stock alert threshold; optional.
             *
             * @var int $low_stock_threshold
             */
            'low_stock_threshold' => 'sometimes|integer|min:0',

            /**
             * SEO meta title; nullable.
             *
             * @var string|null $meta_title
             */
            'meta_title' => 'nullable|string|max:255',

            /**
             * SEO meta description; nullable.
             *
             * @var string|null $meta_description
             */
            'meta_description' => 'nullable|string|max:500',

            /**
             * SEO meta keywords; nullable.
             *
             * @var string|null $meta_keywords
             */
            'meta_keywords' => 'nullable|string|max:255',

            /**
             * Canonical URL; nullable.
             *
             * @var string|null $canonical_url
             */
            'canonical_url' => 'nullable|url|max:255',

            /**
             * Publish timestamp when status is active; nullable.
             *
             * @var string|null $published_at
             */
            'published_at' => 'nullable|date',
        ];
    }
}
