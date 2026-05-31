<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\SmsLogStatus;
use App\Models\Central\SmsLog;
use Illuminate\Database\Seeder;

/**
 * Seed demo SMS delivery logs.
 */
class SmsLogSeeder extends Seeder
{
    public function run(): void
    {
        $logs = [
            [
                'recipient' => '+2348012345678',
                'message' => 'Your Softmax Tech trial ends in 3 days. Ensure your card is saved for uninterrupted service.',
                'status' => SmsLogStatus::Delivered,
                'provider' => 'twilio',
                'provider_message_id' => 'SM_delivered_demo_001',
                'cost' => 4.5000,
                'sent_at' => now()->subDays(2),
                'delivered_at' => now()->subDays(2)->addMinutes(1),
            ],
            [
                'recipient' => '+2348098765432',
                'message' => 'Payment of NGN 25,000.00 received. Your subscription is active.',
                'status' => SmsLogStatus::Sent,
                'provider' => 'twilio',
                'provider_message_id' => 'SM_sent_demo_002',
                'cost' => 4.5000,
                'sent_at' => now()->subDay(),
            ],
            [
                'recipient' => '+2348076543210',
                'message' => 'We could not process your renewal payment. Please update your card.',
                'status' => SmsLogStatus::Failed,
                'provider' => 'twilio',
                'error_message' => 'Invalid phone number format',
            ],
        ];

        foreach ($logs as $log) {
            SmsLog::query()->updateOrCreate(
                [
                    'recipient' => $log['recipient'],
                    'message' => $log['message'],
                ],
                $log,
            );
        }
    }
}
