<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Root seeder executed for each tenant database after migrations.
 */
class TenantDatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            TenantRolePermissionSeeder::class,
            TenantInitialDataSeeder::class,
        ]);
    }
}
