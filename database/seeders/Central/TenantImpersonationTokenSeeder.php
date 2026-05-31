<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\TenantImpersonationToken;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed admin impersonation tokens for demo tenants.
 */
class TenantImpersonationTokenSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TenantImpersonationToken::query()->updateOrCreate(
            [
                'tenant_id' => $this->tenant('acme-corp')->id,
                'token' => hash('sha256', 'token_acme_active'),
            ],
            [
                'admin_id' => $this->userId('admin@platform.com'),
                'expires_at' => now()->addHour(),
                'used_at' => null,
            ],
        );

        TenantImpersonationToken::query()->updateOrCreate(
            [
                'tenant_id' => $this->tenant('beta-solutions')->id,
                'token' => hash('sha256', 'token_beta_used'),
            ],
            [
                'admin_id' => $this->userId('alice.support@platform.com'),
                'expires_at' => now()->subHour(),
                'used_at' => now()->subMinutes(30),
            ],
        );

        TenantImpersonationToken::query()->updateOrCreate(
            [
                'tenant_id' => $this->tenant('gamma-innovations')->id,
                'token' => hash('sha256', 'token_gamma_active'),
            ],
            [
                'admin_id' => $this->userId('admin@platform.com'),
                'expires_at' => now()->addDay(),
                'used_at' => null,
            ],
        );
    }
}
