<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\Plan;
use Illuminate\Database\Seeder;

/**
 * Seed subscription plans with marketing display copy (plans.features).
 *
 * Enforceable limits live in plan_features — seeded by {@see PlanFeatureSeeder}.
 */
class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'starter',
                'name' => 'Starter',
                'description' => 'Perfect for small businesses getting started.',
                'tier' => 1,
                'is_active' => true,
                'is_public' => true,
                'price_monthly' => 2_500_000,
                'price_yearly' => 25_000_000,
                'currency' => 'NGN',
                'trial_days' => 14,
                'sort_order' => 1,
                'features' => [
                    'highlights' => [
                        'Up to 100 products',
                        '3 team members',
                        '10 GB storage',
                        'API access',
                        'Email support',
                    ],
                ],
            ],
            [
                'slug' => 'professional',
                'name' => 'Professional',
                'description' => 'For growing businesses with advanced needs.',
                'tier' => 2,
                'is_active' => true,
                'is_public' => true,
                'price_monthly' => 7_500_000,
                'price_yearly' => 75_000_000,
                'currency' => 'NGN',
                'trial_days' => 14,
                'sort_order' => 2,
                'features' => [
                    'highlights' => [
                        'Up to 1,000 products',
                        '10 team members',
                        '50 GB storage',
                        'API access',
                        'Custom domain',
                        'Priority support',
                    ],
                    'badge' => 'Most popular',
                ],
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'Full-featured solution for large organizations.',
                'tier' => 3,
                'is_active' => true,
                'is_public' => true,
                'price_monthly' => 20_000_000,
                'price_yearly' => 200_000_000,
                'currency' => 'NGN',
                'trial_days' => 30,
                'sort_order' => 3,
                'features' => [
                    'highlights' => [
                        'Unlimited products',
                        'Unlimited team members',
                        '500 GB storage',
                        'API access',
                        'Custom domain',
                        'Dedicated account manager',
                        '99.99% SLA uptime',
                    ],
                    'badge' => 'Best for scale',
                ],
            ],
            [
                'slug' => 'legacy-basic',
                'name' => 'Legacy Basic',
                'description' => 'Deprecated basic plan — no longer offered on signup.',
                'tier' => 1,
                'is_active' => false,
                'is_public' => false,
                'price_monthly' => 1_500_000,
                'price_yearly' => 15_000_000,
                'currency' => 'NGN',
                'trial_days' => 7,
                'sort_order' => 0,
                'features' => [
                    'highlights' => [
                        'Up to 50 products',
                        'Legacy plan — contact support to migrate',
                    ],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            $slug = $plan['slug'];
            unset($plan['slug']);

            Plan::query()->updateOrCreate(
                ['slug' => $slug],
                $plan,
            );
        }

        $this->call(PlanFeatureSeeder::class);
    }
}
