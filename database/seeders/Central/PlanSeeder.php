<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\Plan;
use Illuminate\Database\Seeder;

/**
 * Seed subscription plans (reference data).
 */
class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            ['slug' => 'starter', 'state' => 'starter'],
            ['slug' => 'professional', 'state' => 'professional'],
            ['slug' => 'enterprise', 'state' => 'enterprise'],
            ['slug' => 'legacy-basic', 'state' => 'legacy'],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                Plan::factory()->{$plan['state']}()->make()->toArray(),
            );
        }
    }
}
