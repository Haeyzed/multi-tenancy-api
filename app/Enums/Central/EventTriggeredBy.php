<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\SubscriptionEvent;

/**
 * Actor that triggered a subscription lifecycle event.
 *
 * Stored on {@see SubscriptionEvent::$triggered_by} in the `subscription_events` table.
 */
enum EventTriggeredBy: string implements HasLabel
{
    use InteractsWithEnum;

    case User = 'user';
    case System = 'system';
    case Admin = 'admin';
    case Payment = 'payment';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::System => 'System',
            self::Admin => 'Admin',
            self::Payment => 'Payment',
        };
    }
}
