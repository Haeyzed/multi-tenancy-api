<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Services\Tenant\TenantBootstrapService;
use Illuminate\Database\Seeder;

/**
 * Seed tenant-wide defaults (general settings + primary store) after migrations.
 */
class TenantInitialDataSeeder extends Seeder
{
    public function run(): void
    {
        app(TenantBootstrapService::class)->bootstrap();
    }
}
