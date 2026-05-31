<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\PushDeviceType;
use App\Models\Central\PushNotificationToken;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed demo push notification device tokens.
 */
class PushNotificationTokenSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    public function run(): void
    {
        $tokens = [
            [
                'user' => 'david.tech@platform.com',
                'device_type' => PushDeviceType::Web,
                'device_token' => 'web_push_token_demo_david_001',
                'is_active' => true,
                'last_used_at' => now()->subHours(3),
            ],
            [
                'user' => 'admin@platform.com',
                'device_type' => PushDeviceType::Ios,
                'device_token' => 'apns_token_demo_admin_001',
                'is_active' => true,
                'last_used_at' => now()->subDay(),
            ],
            [
                'user' => 'carol.billing@platform.com',
                'device_type' => PushDeviceType::Android,
                'device_token' => 'fcm_token_demo_carol_001',
                'is_active' => true,
                'last_used_at' => now()->subHours(6),
            ],
        ];

        foreach ($tokens as $token) {
            $user = $this->user($token['user']);

            PushNotificationToken::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_token' => $token['device_token'],
                ],
                [
                    'device_type' => $token['device_type'],
                    'is_active' => $token['is_active'],
                    'last_used_at' => $token['last_used_at'],
                ],
            );
        }
    }
}
