<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\UsageRecord;

/**
 * Billable usage dimension tracked for a tenant.
 *
 * Stored on {@see UsageRecord::$metric} in the `usage_records` table.
 */
enum UsageMetric: string implements HasLabel
{
    use InteractsWithEnum;

    case Products = 'products';
    case Orders = 'orders';
    case Storage = 'storage';
    case Bandwidth = 'bandwidth';
    case Staff = 'staff';
    case Transactions = 'transactions';
    case ApiCalls = 'api_calls';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Products => 'Products',
            self::Orders => 'Orders',
            self::Storage => 'Storage',
            self::Bandwidth => 'Bandwidth',
            self::Staff => 'Staff',
            self::Transactions => 'Transactions',
            self::ApiCalls => 'API Calls',
        };
    }
}
