<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\UserRole;
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
            ['factory' => User::factory()->superAdmin(), 'roles' => [UserRole::SuperAdmin->value]],
            ['factory' => User::factory()->support('Alice Johnson', 'alice.support@platform.com'), 'roles' => [UserRole::Support->value]],
            ['factory' => User::factory()->support('Bob Williams', 'bob.support@platform.com'), 'roles' => [UserRole::Support->value]],
            ['factory' => User::factory()->billing('Carol Davis', 'carol.billing@platform.com'), 'roles' => [UserRole::Billing->value]],
            ['factory' => User::factory()->technical('David Miller', 'david.tech@platform.com'), 'roles' => [UserRole::Technical->value]],
            ['factory' => User::factory()->technical('Eve Wilson', 'eve.tech@platform.com', active: false), 'roles' => [UserRole::Technical->value]],
        ];

        foreach ($users as $entry) {
            $attributes = $entry['factory']->make();

            $user = User::query()->updateOrCreate(
                ['email' => $attributes->email],
                $attributes->getAttributes(),
            );

            $user->syncRoles($entry['roles']);
        }
    }
}
