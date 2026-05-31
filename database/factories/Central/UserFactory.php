<?php

declare(strict_types=1);

namespace Database\Factories\Central;

use App\Enums\Central\UserRole;
use App\Models\Central\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Factory for {@see User} platform administrator models.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(UserRole::cases()),
            'last_login_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'is_active' => true,
        ];
    }

    /**
     * Configure the super administrator account.
     */
    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'name' => 'Super Admin',
            'email' => 'admin@platform.com',
            'role' => UserRole::SuperAdmin,
        ]);
    }

    /**
     * Configure a support team administrator account.
     */
    public function support(string $name, string $email): static
    {
        return $this->state(fn () => [
            'name' => $name,
            'email' => $email,
            'role' => UserRole::Support,
        ]);
    }

    /**
     * Configure a billing team administrator account.
     */
    public function billing(string $name, string $email): static
    {
        return $this->state(fn () => [
            'name' => $name,
            'email' => $email,
            'role' => UserRole::Billing,
        ]);
    }

    /**
     * Configure a technical team administrator account.
     */
    public function technical(string $name, string $email, bool $active = true): static
    {
        return $this->state(fn () => [
            'name' => $name,
            'email' => $email,
            'role' => UserRole::Technical,
            'is_active' => $active,
        ]);
    }
}
