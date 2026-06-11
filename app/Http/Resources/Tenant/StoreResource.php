<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Store
 */
class StoreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example "9f3c2b1a-4d5e-6f7a-8b9c-0d1e2f3a4b5c" */
            'id' => $this->id,
            /** @example "Downtown Flagship" */
            'name' => $this->name,
            /** @example "downtown-flagship" */
            'slug' => $this->slug,
            /** @example "DT-001" */
            'code' => $this->code,
            /** @example "retail" */
            'type' => $this->type,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'website_url' => $this->website_url,
            'address' => $this->address,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'tax_rate' => $this->tax_rate,
            'opening_hours' => $this->opening_hours,
            'manager_id' => $this->manager_id,
            'logo_media_id' => $this->logo_media_id,
            'favicon_media_id' => $this->favicon_media_id,
            /** @example true */
            'is_primary' => (bool) $this->is_primary,
            /** @example true */
            'is_active' => (bool) $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'logo_media' => new MediaResource($this->whenLoaded('logoMedia')),
            'favicon_media' => new MediaResource($this->whenLoaded('faviconMedia')),
            'settings' => new StoreSettingResource($this->whenLoaded('settings')),
            'primary_address' => new StoreAddressResource($this->whenLoaded('primaryAddress')),
            'addresses' => StoreAddressResource::collection($this->whenLoaded('addresses')),
        ];
    }
}
