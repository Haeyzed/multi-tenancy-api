<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Brand
 */
class BrandResource extends JsonResource
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
             * Display name of the brand.
             *
             * @example "Acme"
             */
            'name' => $this->name,

            /**
             * URL-friendly unique slug.
             *
             * @example "acme"
             */
            'slug' => $this->slug,

            /**
             * Marketing description of the brand.
             *
             * @example "Premium outdoor gear"
             *
             * @default null
             */
            'description' => $this->description,

            /**
             * Media library ID for the brand logo.
             *
             * @example 12
             *
             * @default null
             */
            'logo_media_id' => $this->logo_media_id,

            /**
             * Public website URL for the brand.
             *
             * @example "https://acme.example.com"
             *
             * @default null
             */
            'website_url' => $this->website_url,

            /**
             * Whether the brand is visible in the catalog.
             *
             * @example true
             *
             * @default true
             */
            'is_active' => (bool) $this->is_active,

            /**
             * Display order in brand lists.
             *
             * @example 1
             *
             * @default 0
             */
            'sort_order' => $this->sort_order,

            /**
             * Number of products linked to this brand.
             *
             * @default 0
             */
            'products_count' => $this->whenCounted('products', fn (): int => $this->products_count, 0),

            /**
             * Timestamp when the brand was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the brand was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Timestamp when the brand was soft deleted.
             *
             * @default null
             */
            'deleted_at' => $this->deleted_at?->toIso8601String(),

            /**
             * Logo media details when eager loaded.
             *
             * @default null
             */
            'logo_media' => $this->whenLoaded('logoMedia', fn (): array => [
                'id' => $this->logoMedia?->id,
                'file_name' => $this->logoMedia?->file_name,
                'mime_type' => $this->logoMedia?->mime_type,
                'url' => $this->logoMedia?->getUrl(),
            ]),
        ];
    }
}
