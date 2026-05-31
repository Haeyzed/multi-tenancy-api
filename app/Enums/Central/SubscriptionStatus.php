<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\Subscription;

/**
 * Current state of a tenant subscription.
 *
 * Stored on {@see Subscription::$status} in the `subscriptions` table.
 */
enum SubscriptionStatus: string implements HasLabel
{
    use InteractsWithEnum;

    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Paused = 'paused';
    case Expired = 'expired';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Trialing',
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Cancelled => 'Cancelled',
            self::Paused => 'Paused',
            self::Expired => 'Expired',
        };
    }
}
