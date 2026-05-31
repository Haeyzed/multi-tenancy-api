<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\SubscriptionEvent;

/**
 * Subscription lifecycle event recorded for auditing.
 *
 * Stored on {@see SubscriptionEvent::$event_type} in the `subscription_events` table.
 */
enum SubscriptionEventType: string implements HasLabel
{
    use InteractsWithEnum;

    case Created = 'created';
    case Upgraded = 'upgraded';
    case Downgraded = 'downgraded';
    case Renewed = 'renewed';
    case Cancelled = 'cancelled';
    case Reactivated = 'reactivated';
    case TrialEnded = 'trial_ended';
    case PaymentFailed = 'payment_failed';
    case PaymentSucceeded = 'payment_succeeded';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::Upgraded => 'Upgraded',
            self::Downgraded => 'Downgraded',
            self::Renewed => 'Renewed',
            self::Cancelled => 'Cancelled',
            self::Reactivated => 'Reactivated',
            self::TrialEnded => 'Trial Ended',
            self::PaymentFailed => 'Payment Failed',
            self::PaymentSucceeded => 'Payment Succeeded',
        };
    }
}
