<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\FeatureType;
use App\Models\Central\PlanFeature;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed structured plan feature records.
 */
class PlanFeatureSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            ['plan' => 'starter', 'feature_key' => 'max_products', 'feature_value' => '100', 'feature_type' => FeatureType::Integer],
            ['plan' => 'starter', 'feature_key' => 'api_access', 'feature_value' => 'true', 'feature_type' => FeatureType::Boolean],
            ['plan' => 'professional', 'feature_key' => 'max_products', 'feature_value' => '1000', 'feature_type' => FeatureType::Integer],
            ['plan' => 'professional', 'feature_key' => 'custom_domain', 'feature_value' => 'true', 'feature_type' => FeatureType::Boolean],
            ['plan' => 'professional', 'feature_key' => 'storage_limit_gb', 'feature_value' => '50.00', 'feature_type' => FeatureType::Decimal],
            ['plan' => 'enterprise', 'feature_key' => 'max_products', 'feature_value' => 'unlimited', 'feature_type' => FeatureType::String],
            ['plan' => 'enterprise', 'feature_key' => 'sla_uptime', 'feature_value' => '99.99', 'feature_type' => FeatureType::Decimal],
        ];

        foreach ($features as $feature) {
            PlanFeature::query()->updateOrCreate(
                [
                    'plan_id' => $this->plan($feature['plan'])->id,
                    'feature_key' => $feature['feature_key'],
                ],
                [
                    'feature_value' => $feature['feature_value'],
                    'feature_type' => $feature['feature_type'],
                ],
            );
        }
    }
}
