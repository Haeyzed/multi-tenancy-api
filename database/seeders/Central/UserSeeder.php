<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\Central\User;
use Illuminate\Database\Seeder;

/**
 * Seed deterministic platform administrator accounts.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            User::factory()->superAdmin(),
            User::factory()->support('Alice Johnson', 'alice.support@platform.com'),
            User::factory()->support('Bob Williams', 'bob.support@platform.com'),
            User::factory()->billing('Carol Davis', 'carol.billing@platform.com'),
            User::factory()->technical('David Miller', 'david.tech@platform.com'),
            User::factory()->technical('Eve Wilson', 'eve.tech@platform.com', active: false),
        ];

        foreach ($users as $factory) {
            $attributes = $factory->make();

            $user = User::query()->updateOrCreate(
                ['email' => $attributes->email],
                $attributes->getAttributes(),
            );

            $user->syncRoles([$user->role->value]);
        }
    }
}
