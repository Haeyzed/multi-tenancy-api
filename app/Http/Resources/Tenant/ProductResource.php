<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier.
             *
             * @example "9f3c2b1a-4e5d-6f7a-8b9c-0d1e2f3a4b5c"
             */
            'id' => $this->id,

            /**
             * Display name of the product.
             *
             * @example "Wireless Headphones"
             */
            'name' => $this->name,

            /**
             * URL-friendly unique slug.
             *
             * @example "wireless-headphones"
             */
            'slug' => $this->slug,

            /**
             * Stock keeping unit.
             *
             * @example "WH-001"
             */
            'sku' => $this->sku,

            /**
             * Barcode value.
             *
             * @default null
             */
            'barcode' => $this->barcode,

            /**
             * Full product description.
             *
             * @default null
             */
            'description' => $this->description,

            /**
             * Short summary for listings.
             *
             * @default null
             */
            'short_description' => $this->short_description,

            /**
             * Brand ID.
             *
             * @default null
             */
            'brand_id' => $this->brand_id,

            /**
             * Primary category ID.
             *
             * @default null
             */
            'category_id' => $this->category_id,

            /**
             * Product type.
             *
             * @example "simple"
             */
            'type' => $this->type,

            /**
             * Lifecycle status.
             *
             * @example "active"
             */
            'status' => $this->status,

            /**
             * Storefront visibility.
             *
             * @example "visible"
             */
            'visibility' => $this->visibility,

            /**
             * Selling price.
             *
             * @example "29.99"
             */
            'price' => $this->price,

            /**
             * Compare-at price.
             *
             * @default null
             */
            'compare_price' => $this->compare_price,

            /**
             * Cost price.
             *
             * @default null
             */
            'cost_price' => $this->cost_price,

            /**
             * Whether the product requires shipping.
             *
             * @default true
             */
            'requires_shipping' => (bool) $this->requires_shipping,

            /**
             * Whether the product is featured.
             *
             * @default false
             */
            'is_featured' => (bool) $this->is_featured,

            /**
             * Whether the product is a gift card.
             *
             * @default false
             */
            'is_gift_card' => (bool) $this->is_gift_card,

            /**
             * Whether backorders are allowed.
             *
             * @default false
             */
            'allow_backorders' => (bool) $this->allow_backorders,

            /**
             * Low stock alert threshold.
             *
             * @default 10
             */
            'low_stock_threshold' => $this->low_stock_threshold,

            /**
             * SEO meta title.
             *
             * @default null
             */
            'meta_title' => $this->meta_title,

            /**
             * SEO meta description.
             *
             * @default null
             */
            'meta_description' => $this->meta_description,

            /**
             * SEO meta keywords.
             *
             * @default null
             */
            'meta_keywords' => $this->meta_keywords,

            /**
             * Canonical URL.
             *
             * @default null
             */
            'canonical_url' => $this->canonical_url,

            /**
             * Publish timestamp.
             *
             * @default null
             */
            'published_at' => $this->published_at?->toIso8601String(),

            /**
             * Timestamp when the product was created.
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the product was last updated.
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Timestamp when the product was soft deleted.
             *
             * @default null
             */
            'deleted_at' => $this->deleted_at?->toIso8601String(),

            /**
             * Brand when eager loaded.
             *
             * @default null
             */
            'brand' => new BrandResource($this->whenLoaded('brand')),

            /**
             * Category when eager loaded.
             *
             * @default null
             */
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
