<?php

declare(strict_types=1);

namespace Database\Factories\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\TenantStatus;
use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for {@see Tenant} models in the central database.
 *
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    private function tenantDomain(string $slug): string
    {
        return $slug.'.'.config('tenancy.tenant_domain_base', 'multi-tenancy-api.test');
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'database' => 'tenant_'.$slug,
            'domain' => $this->tenantDomain($slug),
            'status' => TenantStatus::Active,
            'plan_id' => Plan::factory(),
            'billing_cycle' => fake()->randomElement(BillingCycle::cases()),
            'trial_ends_at' => fake()->optional()->dateTimeBetween('-30 days', '+30 days'),
            'subscribed_at' => fake()->optional()->dateTimeBetween('-90 days', 'now'),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+365 days'),
            'owner_email' => fake()->safeEmail(),
            'owner_name' => fake()->name(),
            'settings' => ['theme' => fake()->randomElement(['light', 'dark', 'auto']), 'locale' => 'en'],
            'meta' => ['industry' => fake()->word(), 'employees' => fake()->numberBetween(5, 500)],
            'data' => null,
        ];
    }

    /**
     * Configure the Acme Corp demo tenant.
     */
    public function acme(): static
    {
        return $this->state(fn () => [
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'database' => 'tenant_acme_corp',
            'domain' => $this->tenantDomain('acme-corp'),
            'status' => TenantStatus::Active,
            'billing_cycle' => BillingCycle::Monthly,
            'trial_ends_at' => now()->subDays(45),
            'subscribed_at' => now()->subDays(30),
            'expires_at' => now()->addDays(30),
            'owner_email' => 'john@acme.com',
            'owner_name' => 'John Doe',
            'settings' => ['theme' => 'light', 'locale' => 'en'],
            'meta' => ['industry' => 'retail', 'employees' => 25],
            'data' => ['custom_branding' => true],
        ]);
    }

    /**
     * Configure the Beta Solutions demo tenant.
     */
    public function beta(): static
    {
        return $this->state(fn () => [
            'name' => 'Beta Solutions',
            'slug' => 'beta-solutions',
            'database' => 'tenant_beta_solutions',
            'domain' => $this->tenantDomain('beta-solutions'),
            'status' => TenantStatus::Active,
            'billing_cycle' => BillingCycle::Yearly,
            'trial_ends_at' => now()->subDays(90),
            'subscribed_at' => now()->subDays(80),
            'expires_at' => now()->addDays(285),
            'owner_email' => 'sarah@beta.com',
            'owner_name' => 'Sarah Chen',
            'settings' => ['theme' => 'dark', 'locale' => 'en'],
            'meta' => ['industry' => 'tech', 'employees' => 50],
            'data' => null,
        ]);
    }

    /**
     * Configure the Gamma Innovations demo tenant.
     */
    public function gamma(): static
    {
        return $this->state(fn () => [
            'name' => 'Gamma Innovations',
            'slug' => 'gamma-innovations',
            'database' => 'tenant_gamma_innovations',
            'domain' => $this->tenantDomain('gamma-innovations'),
            'status' => TenantStatus::Pending,
            'billing_cycle' => BillingCycle::Monthly,
            'trial_ends_at' => now()->addDays(10),
            'subscribed_at' => null,
            'expires_at' => null,
            'owner_email' => 'mike@gamma.com',
            'owner_name' => 'Mike Ross',
            'settings' => ['theme' => 'auto', 'locale' => 'en'],
            'meta' => null,
            'data' => null,
        ]);
    }

    /**
     * Configure the Delta Works demo tenant.
     */
    public function delta(): static
    {
        return $this->state(fn () => [
            'name' => 'Delta Works',
            'slug' => 'delta-works',
            'database' => 'tenant_delta_works',
            'domain' => $this->tenantDomain('delta-works'),
            'status' => TenantStatus::Suspended,
            'billing_cycle' => BillingCycle::Monthly,
            'trial_ends_at' => now()->subDays(120),
            'subscribed_at' => now()->subDays(100),
            'expires_at' => now()->subDays(5),
            'owner_email' => 'lisa@delta.com',
            'owner_name' => 'Lisa Wong',
            'settings' => ['theme' => 'light', 'locale' => 'en'],
            'meta' => ['industry' => 'manufacturing', 'employees' => 200],
            'data' => null,
        ]);
    }

    /**
     * Configure the Epsilon Ltd demo tenant.
     */
    public function epsilon(): static
    {
        return $this->state(fn () => [
            'name' => 'Epsilon Ltd',
            'slug' => 'epsilon-ltd',
            'database' => 'tenant_epsilon_ltd',
            'domain' => $this->tenantDomain('epsilon-ltd'),
            'status' => TenantStatus::Cancelled,
            'plan_id' => null,
            'billing_cycle' => BillingCycle::Monthly,
            'trial_ends_at' => now()->subDays(200),
            'subscribed_at' => now()->subDays(180),
            'expires_at' => now()->subDays(60),
            'owner_email' => 'tom@epsilon.com',
            'owner_name' => 'Tom Hardy',
            'settings' => null,
            'meta' => null,
            'data' => null,
        ]);
    }
}
