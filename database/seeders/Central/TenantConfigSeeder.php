<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\TenantConfig;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed tenant configuration key-value pairs.
 */
class TenantConfigSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            ['tenant' => 'acme-corp', 'key' => 'mail_driver', 'value' => 'smtp', 'encrypted' => false],
            ['tenant' => 'acme-corp', 'key' => 'stripe_secret_key', 'value' => 'sk_test_xxxxxxxxxxxx', 'encrypted' => true],
            ['tenant' => 'beta-solutions', 'key' => 'mail_driver', 'value' => 'sendgrid', 'encrypted' => false],
            ['tenant' => 'gamma-innovations', 'key' => 'custom_css', 'value' => 'body { background: #f5f5f5; }', 'encrypted' => false],
        ];

        foreach ($configs as $config) {
            TenantConfig::query()->updateOrCreate(
                [
                    'tenant_id' => $this->tenant($config['tenant'])->id,
                    'key' => $config['key'],
                ],
                [
                    'value' => $config['value'],
                    'encrypted' => $config['encrypted'],
                ],
            );
        }
    }
}
