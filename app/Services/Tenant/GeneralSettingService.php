<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\GeneralSetting;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-wide general settings (singleton per tenant database).
 */
class GeneralSettingService
{
    /**
     * Default values applied when bootstrapping the settings row.
     *
     * @var array<string, mixed>
     */
    private const DEFAULTS = [
        'default_currency' => 'USD',
        'currency_symbol' => '$',
        'currency_position' => 'before',
        'default_timezone' => 'UTC',
        'default_language' => 'en',
        'default_weight_unit' => 'kg',
        'default_dimension_unit' => 'cm',
    ];

    /**
     * Get the tenant general settings record, creating defaults when missing.
     */
    public function get(): GeneralSetting
    {
        return GeneralSetting::query()->first() ?? $this->createDefaults();
    }

    /**
     * Update tenant general settings.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): GeneralSetting
    {
        $settings = $this->get();
        $settings->update($data);

        return $settings->fresh();
    }

    /**
     * KPI card metrics for general settings completeness.
     *
     * @return list<array{key: string, label: string, value: int|string}>
     */
    public function getMetrics(): array
    {
        $settings = $this->get();

        $filledFields = collect([
            $settings->company_name,
            $settings->support_email,
            $settings->billing_email,
            $settings->website_url,
            $settings->email_from_address,
        ])->filter(fn ($value) => filled($value))->count();

        return [
            ['key' => 'configured_fields', 'label' => 'Key Fields Set', 'value' => $filledFields],
            ['key' => 'currency', 'label' => 'Default Currency', 'value' => $settings->default_currency],
            ['key' => 'timezone', 'label' => 'Default Timezone', 'value' => $settings->default_timezone],
        ];
    }

    /**
     * Bootstrap the singleton settings row.
     */
    private function createDefaults(): GeneralSetting
    {
        return DB::transaction(fn (): GeneralSetting => GeneralSetting::query()->create(self::DEFAULTS));
    }
}
