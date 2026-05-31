<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\UserNotificationChannel;
use App\Models\Central\UserNotification;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed demo in-app notifications for platform administrators.
 */
class NotificationSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    public function run(): void
    {
        $billingUser = $this->user('carol.billing@platform.com');
        $superAdmin = $this->user('admin@platform.com');

        $notifications = [
            [
                'user_id' => $billingUser->id,
                'type' => 'tenant.onboarded',
                'title' => 'New tenant onboarded',
                'body' => 'Acme Corp completed signup and started a trial on the Starter plan.',
                'data' => ['tenant_slug' => 'acme-corp'],
                'channel' => UserNotificationChannel::InApp,
                'is_read' => false,
                'sent_at' => now()->subHours(2),
            ],
            [
                'user_id' => $billingUser->id,
                'type' => 'payment.confirmed',
                'title' => 'Payment confirmed',
                'body' => 'Acme Corp paid invoice INV-2026-0002 successfully.',
                'data' => ['invoice_number' => 'INV-2026-0002'],
                'channel' => UserNotificationChannel::InApp,
                'is_read' => true,
                'read_at' => now()->subHour(),
                'sent_at' => now()->subDays(1),
            ],
            [
                'user_id' => $superAdmin->id,
                'type' => 'payment.failed',
                'title' => 'Payment failed',
                'body' => 'Delta Works payment failed — card expired.',
                'data' => ['tenant_slug' => 'delta-works'],
                'channel' => UserNotificationChannel::InApp,
                'is_read' => false,
                'sent_at' => now()->subMinutes(30),
            ],
        ];

        foreach ($notifications as $notification) {
            UserNotification::query()->updateOrCreate(
                [
                    'user_id' => $notification['user_id'],
                    'type' => $notification['type'],
                    'title' => $notification['title'],
                ],
                $notification,
            );
        }
    }
}
