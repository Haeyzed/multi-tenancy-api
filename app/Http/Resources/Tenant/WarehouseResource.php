<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Warehouse
 */
class WarehouseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example "9f3c2b1a-4d5e-6f7a-8b9c-0d1e2f3a4b5c" */
            'id' => $this->id,
            'store_id' => $this->store_id,
            /** @example "Central Fulfillment" */
            'name' => $this->name,
            /** @example "WH-001" */
            'code' => $this->code,
            /** @example "main" */
            'type' => $this->type,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'timezone' => $this->timezone,
            'manager_id' => $this->manager_id,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'store' => new StoreResource($this->whenLoaded('store')),
        ];
    }
}
