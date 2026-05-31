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
                'max_products' => fake()->numberBetween(50, 1000),
                'max_staff' => fake()->numberBetween(3, 50),
                'storage_gb' => fake()->numberBetween(10, 500),
                'api_access' => true,
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
            'price_monthly' => 2_500_000,   // ₦25,000
            'price_yearly' => 25_000_000,   // ₦250,000
            'trial_days' => 14,
            'sort_order' => 1,
            'features' => [
                'max_products' => 100,
                'max_staff' => 3,
                'storage_gb' => 10,
                'api_access' => true,
                'custom_domain' => false,
                'priority_support' => false,
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
            'price_monthly' => 7_500_000,   // ₦75,000
            'price_yearly' => 75_000_000, // ₦750,000
            'trial_days' => 14,
            'sort_order' => 2,
            'features' => [
                'max_products' => 1000,
                'max_staff' => 10,
                'storage_gb' => 50,
                'api_access' => true,
                'custom_domain' => true,
                'priority_support' => true,
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
            'price_monthly' => 20_000_000,   // ₦200,000
            'price_yearly' => 200_000_000,   // ₦2,000,000
            'trial_days' => 30,
            'sort_order' => 3,
            'features' => [
                'max_products' => 'unlimited',
                'max_staff' => 'unlimited',
                'storage_gb' => 500,
                'api_access' => true,
                'custom_domain' => true,
                'priority_support' => true,
                'dedicated_manager' => true,
                'sla_guarantee' => true,
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
            'price_monthly' => 1_500_000,   // ₦15,000
            'price_yearly' => 15_000_000,   // ₦150,000
            'trial_days' => 7,
            'sort_order' => 0,
            'features' => ['max_products' => 50],
        ]);
    }
}
