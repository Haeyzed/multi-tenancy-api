<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\ApiKey;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed API keys for demo tenants.
 */
class ApiKeySeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keys = [
            [
                'tenant' => 'acme-corp',
                'name' => 'Production API Key',
                'key_hash' => Hash::make('ak_live_acme_12345'),
                'permissions' => ['read:products', 'write:orders', 'read:customers'],
                'last_used_at' => now()->subHours(2),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'tenant' => 'acme-corp',
                'name' => 'Staging API Key',
                'key_hash' => Hash::make('ak_test_acme_67890'),
                'permissions' => ['read:products', 'read:orders'],
                'last_used_at' => now()->subDays(5),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'tenant' => 'beta-solutions',
                'name' => 'Primary API Key',
                'key_hash' => Hash::make('ak_live_beta_54321'),
                'permissions' => ['*'],
                'last_used_at' => now()->subMinutes(30),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'tenant' => 'gamma-innovations',
                'name' => 'Trial API Key',
                'key_hash' => Hash::make('ak_trial_gamma_99999'),
                'permissions' => ['read:products'],
                'last_used_at' => null,
                'expires_at' => now()->addDays(10),
                'is_active' => true,
            ],
            [
                'tenant' => 'delta-works',
                'name' => 'Expired API Key',
                'key_hash' => Hash::make('ak_old_delta_00000'),
                'permissions' => ['read:products'],
                'last_used_at' => now()->subDays(30),
                'expires_at' => now()->subDays(15),
                'is_active' => false,
            ],
        ];

        foreach ($keys as $key) {
            ApiKey::query()->updateOrCreate(
                [
                    'tenant_id' => $this->tenant($key['tenant'])->id,
                    'name' => $key['name'],
                ],
                [
                    'key_hash' => $key['key_hash'],
                    'permissions' => $key['permissions'],
                    'last_used_at' => $key['last_used_at'],
                    'expires_at' => $key['expires_at'],
                    'is_active' => $key['is_active'],
                ],
            );
        }
    }
}
