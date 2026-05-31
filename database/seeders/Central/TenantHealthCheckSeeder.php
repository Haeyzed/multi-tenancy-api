<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\HealthCheckStatus;
use App\Enums\Central\HealthCheckType;
use App\Models\Central\TenantHealthCheck;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed tenant health check results.
 */
class TenantHealthCheckSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checks = [
            ['tenant' => 'acme-corp', 'check_type' => HealthCheckType::DbConnectivity, 'status' => HealthCheckStatus::Healthy, 'response_time_ms' => 12, 'message' => 'Database connection established in 12ms', 'checked_at' => now()->subMinutes(5)],
            ['tenant' => 'acme-corp', 'check_type' => HealthCheckType::Storage, 'status' => HealthCheckStatus::Healthy, 'response_time_ms' => 45, 'message' => 'Storage usage: 7.5GB / 10GB', 'checked_at' => now()->subMinutes(5)],
            ['tenant' => 'acme-corp', 'check_type' => HealthCheckType::Ssl, 'status' => HealthCheckStatus::Healthy, 'response_time_ms' => 8, 'message' => 'SSL certificate valid until 2027-05-30', 'checked_at' => now()->subMinutes(5)],
            ['tenant' => 'beta-solutions', 'check_type' => HealthCheckType::Storage, 'status' => HealthCheckStatus::Warning, 'response_time_ms' => 120, 'message' => 'Storage usage: 48GB / 50GB (96%)', 'checked_at' => now()->subMinutes(10)],
            ['tenant' => 'beta-solutions', 'check_type' => HealthCheckType::Queue, 'status' => HealthCheckStatus::Healthy, 'response_time_ms' => 25, 'message' => 'Queue processing normally, 0 pending jobs', 'checked_at' => now()->subMinutes(10)],
            ['tenant' => 'delta-works', 'check_type' => HealthCheckType::DbConnectivity, 'status' => HealthCheckStatus::Critical, 'response_time_ms' => null, 'message' => 'Database connection timeout after 30s', 'checked_at' => now()->subMinutes(30)],
            ['tenant' => 'delta-works', 'check_type' => HealthCheckType::Redis, 'status' => HealthCheckStatus::Warning, 'response_time_ms' => 800, 'message' => 'Redis latency elevated: 800ms', 'checked_at' => now()->subMinutes(30)],
        ];

        foreach ($checks as $check) {
            TenantHealthCheck::query()->updateOrCreate(
                [
                    'tenant_id' => $this->tenant($check['tenant'])->id,
                    'check_type' => $check['check_type'],
                ],
                [
                    'status' => $check['status'],
                    'response_time_ms' => $check['response_time_ms'],
                    'message' => $check['message'],
                    'checked_at' => $check['checked_at'],
                ],
            );
        }
    }
}
