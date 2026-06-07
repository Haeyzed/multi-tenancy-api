<?php

declare(strict_types=1);

namespace Database\Factories\Central;

use App\Models\Central\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for {@see Plan} models in the central database.
 *
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => str($name)->slug()->toString(),
            'description' => fake()->sentence(12),
            'tier' => fake()->numberBetween(1, 3),
            'is_active' => true,
            'is_public' => true,
            'price_monthly' => fake()->numberBetween(1_500_000, 20_000_000),
            'price_yearly' => fake()->numberBetween(15_000_000, 200_000_000),
            'currency' => 'NGN',
            'trial_days' => fake()->randomElement([7, 14, 30]),
            'sort_order' => fake()->numberBetween(1, 10),
            'features' => [
                'highlights' => [
                    fake()->sentence(6),
                    fake()->sentence(6),
                ],
            ],
        ];
    }

    /**
     * Configure the Starter plan reference data.
     */
    public function starter(): static
    {
        return $this->state(fn () => [
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Perfect for small businesses getting started.',
            'tier' => 1,
            'currency' => 'NGN',
            'price_monthly' => 2_500_000,
            'price_yearly' => 25_000_000,
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
        ]);
    }

    /**
     * Configure the Professional plan reference data.
     */
    public function professional(): static
    {
        return $this->state(fn () => [
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'For growing businesses with advanced needs.',
            'tier' => 2,
            'currency' => 'NGN',
            'price_monthly' => 7_500_000,
            'price_yearly' => 75_000_000,
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
            ],
        ]);
    }

    /**
     * Configure the Enterprise plan reference data.
     */
    public function enterprise(): static
    {
        return $this->state(fn () => [
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'description' => 'Full-featured solution for large organizations.',
            'tier' => 3,
            'currency' => 'NGN',
            'price_monthly' => 20_000_000,
            'price_yearly' => 200_000_000,
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
                    'SLA guarantee',
                ],
                'badge' => 'Best for scale',
            ],
        ]);
    }

    /**
     * Configure the deprecated Legacy Basic plan reference data.
     */
    public function legacy(): static
    {
        return $this->state(fn () => [
            'name' => 'Legacy Basic',
            'slug' => 'legacy-basic',
            'description' => 'Deprecated basic plan.',
            'tier' => 1,
            'is_active' => false,
            'is_public' => false,
            'currency' => 'NGN',
            'price_monthly' => 1_500_000,
            'price_yearly' => 15_000_000,
            'trial_days' => 7,
            'sort_order' => 0,
            'features' => [
                'highlights' => [
                    'Up to 50 products',
                ],
            ],
        ]);
    }
}
