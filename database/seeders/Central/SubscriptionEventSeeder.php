<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\SubscriptionEventType;
use App\Models\Central\SubscriptionEvent;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed subscription lifecycle events.
 */
class SubscriptionEventSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'tenant' => 'acme-corp',
                'event_type' => SubscriptionEventType::TrialEnded,
                'from_plan' => null,
                'to_plan' => 'starter',
                'triggered_by' => EventTriggeredBy::System,
                'metadata' => ['trial_days' => 14],
            ],
            [
                'tenant' => 'beta-solutions',
                'event_type' => SubscriptionEventType::Upgraded,
                'from_plan' => 'starter',
                'to_plan' => 'professional',
                'triggered_by' => EventTriggeredBy::User,
                'metadata' => ['reason' => 'Need more products'],
            ],
            [
                'tenant' => 'delta-works',
                'event_type' => SubscriptionEventType::PaymentFailed,
                'from_plan' => null,
                'to_plan' => null,
                'triggered_by' => EventTriggeredBy::Payment,
                'metadata' => ['attempt' => 3, 'error' => 'card_expired'],
            ],
            [
                'tenant' => 'epsilon-ltd',
                'event_type' => SubscriptionEventType::Cancelled,
                'from_plan' => 'starter',
                'to_plan' => null,
                'triggered_by' => EventTriggeredBy::User,
                'metadata' => ['reason' => 'Switched to competitor', 'feedback' => 'Too expensive'],
            ],
            [
                'tenant' => 'acme-corp',
                'event_type' => SubscriptionEventType::Renewed,
                'from_plan' => 'starter',
                'to_plan' => 'starter',
                'triggered_by' => EventTriggeredBy::System,
                'metadata' => ['auto_renew' => true],
            ],
        ];

        foreach ($events as $event) {
            SubscriptionEvent::query()->updateOrCreate(
                [
                    'subscription_id' => $this->subscription($event['tenant'])->id,
                    'event_type' => $event['event_type'],
                    'from_plan_id' => $event['from_plan'] ? $this->plan($event['from_plan'])->id : null,
                    'to_plan_id' => $event['to_plan'] ? $this->plan($event['to_plan'])->id : null,
                ],
                [
                    'triggered_by' => $event['triggered_by'],
                    'metadata' => $event['metadata'],
                ],
            );
        }
    }
}
