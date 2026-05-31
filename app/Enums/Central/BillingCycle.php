<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;

/**
 * Billing frequency applied to tenants and subscriptions.
 *
 * Stored on {@see Tenant::$billing_cycle} and {@see Subscription::$billing_cycle}.
 */
enum BillingCycle: string implements HasLabel
{
    use InteractsWithEnum;

    case Monthly = 'monthly';
    case Yearly = 'yearly';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Yearly => 'Yearly',
        };
    }
}
