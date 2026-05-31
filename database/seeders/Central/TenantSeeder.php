<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\Tenant;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed demo tenants via factory states.
 */
class TenantSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            ['slug' => 'acme-corp', 'state' => 'acme', 'plan' => 'starter'],
            ['slug' => 'beta-solutions', 'state' => 'beta', 'plan' => 'professional'],
            ['slug' => 'gamma-innovations', 'state' => 'gamma', 'plan' => 'starter'],
            ['slug' => 'delta-works', 'state' => 'delta', 'plan' => 'enterprise'],
            ['slug' => 'epsilon-ltd', 'state' => 'epsilon', 'plan' => null],
        ];

        foreach ($tenants as $tenant) {
            $attributes = Tenant::factory()->{$tenant['state']}()->make()->toArray();

            if ($tenant['plan'] !== null) {
                $attributes['plan_id'] = $this->plan($tenant['plan'])->id;
            }

            Tenant::query()->updateOrCreate(
                ['slug' => $tenant['slug']],
                $attributes,
            );
        }
    }
}
