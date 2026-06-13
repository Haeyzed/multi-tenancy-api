<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * Parent category ID when nested.
             *
             * @default null
             */
            'parent_id' => $this->parent_id,

            /**
             * Display name of the category.
             *
             * @example "Electronics"
             */
            'name' => $this->name,

            /**
             * URL-friendly unique slug.
             *
             * @example "electronics"
             */
            'slug' => $this->slug,

            /**
             * Marketing description of the category.
             *
             * @default null
             */
            'description' => $this->description,

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
             * Banner media ID.
             *
             * @default null
             */
            'banner_media_id' => $this->banner_media_id,

            /**
             * Icon media ID.
             *
             * @default null
             */
            'icon_media_id' => $this->icon_media_id,

            /**
             * Display order in category lists.
             *
             * @default 0
             */
            'sort_order' => $this->sort_order,

            /**
             * Whether the category is visible in the catalog.
             *
             * @default true
             */
            'is_active' => (bool) $this->is_active,

            /**
             * Whether the category is featured.
             *
             * @default false
             */
            'is_featured' => (bool) $this->is_featured,

            /**
             * Whether the category appears in navigation menus.
             *
             * @default true
             */
            'show_in_menu' => (bool) $this->show_in_menu,

            /**
             * Tree depth (0 for root categories).
             *
             * @default 0
             */
            'depth' => $this->depth,

            /**
             * Materialized path for tree ordering.
             *
             * @default null
             */
            'path' => $this->path,

            /**
             * Number of products linked to this category.
             *
             * @default 0
             */
            'products_count' => $this->whenCounted('products', fn (): int => $this->products_count, 0),

            /**
             * Timestamp when the category was created.
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the category was last updated.
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Timestamp when the category was soft deleted.
             *
             * @default null
             */
            'deleted_at' => $this->deleted_at?->toIso8601String(),

            /**
             * Parent category when eager loaded.
             *
             * @default null
             */
            'parent' => new self($this->whenLoaded('parent')),

            /**
             * Banner media details when eager loaded.
             *
             * @default null
             */
            'banner_media' => $this->whenLoaded('bannerMedia', fn (): array => [
                'id' => $this->bannerMedia?->id,
                'file_name' => $this->bannerMedia?->file_name,
                'mime_type' => $this->bannerMedia?->mime_type,
                'url' => $this->bannerMedia?->getUrl(),
            ]),

            /**
             * Icon media details when eager loaded.
             *
             * @default null
             */
            'icon_media' => $this->whenLoaded('iconMedia', fn (): array => [
                'id' => $this->iconMedia?->id,
                'file_name' => $this->iconMedia?->file_name,
                'mime_type' => $this->iconMedia?->mime_type,
                'url' => $this->iconMedia?->getUrl(),
            ]),
        ];
    }
}
