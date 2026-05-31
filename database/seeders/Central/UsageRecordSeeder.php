<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\UsageMetric;
use App\Models\Central\UsageRecord;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed subscription usage records.
 */
class UsageRecordSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            ['tenant' => 'acme-corp', 'metric' => UsageMetric::Products, 'quantity' => 85.00, 'recorded_at' => now()->subDays(2)],
            ['tenant' => 'acme-corp', 'metric' => UsageMetric::Storage, 'quantity' => 7500.50, 'recorded_at' => now()->subDays(2)],
            ['tenant' => 'beta-solutions', 'metric' => UsageMetric::Orders, 'quantity' => 1240.00, 'recorded_at' => now()->subDays(5)],
            ['tenant' => 'beta-solutions', 'metric' => UsageMetric::ApiCalls, 'quantity' => 45000.00, 'recorded_at' => now()->subDays(5)],
            ['tenant' => 'gamma-innovations', 'metric' => UsageMetric::Staff, 'quantity' => 2.00, 'recorded_at' => now()->subDays(1)],
            ['tenant' => 'delta-works', 'metric' => UsageMetric::Bandwidth, 'quantity' => 125000.00, 'recorded_at' => now()->subDays(10)],
        ];

        foreach ($records as $record) {
            $tenant = $this->tenant($record['tenant']);

            UsageRecord::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $this->subscription($record['tenant'])->id,
                    'metric' => $record['metric'],
                ],
                [
                    'quantity' => $record['quantity'],
                    'recorded_at' => $record['recorded_at'],
                ],
            );
        }
    }
}
