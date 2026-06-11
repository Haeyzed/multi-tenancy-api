<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\GeneralSetting;
use App\Models\Tenant\Store;
use App\Models\Tenant\StoreSetting;

/**
 * Per-store operational settings.
 */
class StoreSettingService
{
    /**
     * Get settings for a store, creating defaults when missing.
     */
    public function getForStore(Store $store): StoreSetting
    {
        return StoreSetting::query()->firstOrCreate(
            ['store_id' => $store->id],
            $this->defaultPayload(),
        );
    }

    /**
     * Update settings for a store.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateForStore(Store $store, array $data): StoreSetting
    {
        $settings = $this->getForStore($store);
        $settings->update($data);

        return $settings->fresh('store');
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultPayload(): array
    {
        $general = GeneralSetting::query()->first();

        return [
            'currency' => $general?->default_currency ?? 'USD',
            'default_language' => $general?->default_language ?? 'en',
            'timezone' => $general?->default_timezone ?? 'UTC',
            'weight_unit' => $general?->default_weight_unit ?? 'kg',
            'dimension_unit' => $general?->default_dimension_unit ?? 'cm',
            'primary_color' => '#3B82F6',
            'secondary_color' => '#10B981',
            'order_number_prefix' => 'ORD-',
            'order_number_start' => 1000,
        ];
    }
}
