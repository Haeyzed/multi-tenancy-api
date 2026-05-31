<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\TenantMetric;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed daily tenant usage metrics.
 */
class TenantMetricSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metrics = [
            [
                'tenant' => 'acme-corp',
                'metric_date' => now()->toDateString(),
                'total_orders' => 145,
                'total_revenue' => 2895.50,
                'total_products' => 85,
                'total_customers' => 320,
                'storage_used_mb' => 7500,
                'bandwidth_used_mb' => 45000,
                'api_calls' => 12500,
            ],
            [
                'tenant' => 'acme-corp',
                'metric_date' => now()->subDay()->toDateString(),
                'total_orders' => 132,
                'total_revenue' => 2640.00,
                'total_products' => 84,
                'total_customers' => 315,
                'storage_used_mb' => 7450,
                'bandwidth_used_mb' => 42000,
                'api_calls' => 11000,
            ],
            [
                'tenant' => 'beta-solutions',
                'metric_date' => now()->toDateString(),
                'total_orders' => 890,
                'total_revenue' => 17800.00,
                'total_products' => 450,
                'total_customers' => 1200,
                'storage_used_mb' => 48000,
                'bandwidth_used_mb' => 125000,
                'api_calls' => 45000,
            ],
            [
                'tenant' => 'gamma-innovations',
                'metric_date' => now()->toDateString(),
                'total_orders' => 5,
                'total_revenue' => 0.00,
                'total_products' => 12,
                'total_customers' => 3,
                'storage_used_mb' => 150,
                'bandwidth_used_mb' => 800,
                'api_calls' => 200,
            ],
        ];

        foreach ($metrics as $metric) {
            TenantMetric::query()->updateOrCreate(
                [
                    'tenant_id' => $this->tenant($metric['tenant'])->id,
                    'metric_date' => $metric['metric_date'],
                ],
                [
                    'total_orders' => $metric['total_orders'],
                    'total_revenue' => $metric['total_revenue'],
                    'total_products' => $metric['total_products'],
                    'total_customers' => $metric['total_customers'],
                    'storage_used_mb' => $metric['storage_used_mb'],
                    'bandwidth_used_mb' => $metric['bandwidth_used_mb'],
                    'api_calls' => $metric['api_calls'],
                ],
            );
        }
    }
}
