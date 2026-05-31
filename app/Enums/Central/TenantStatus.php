<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\Tenant;

/**
 * Lifecycle status of a tenant on the platform.
 *
 * Stored on {@see Tenant::$status} in the `tenants` table.
 */
enum TenantStatus: string implements HasLabel
{
    use InteractsWithEnum;

    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Cancelled => 'Cancelled',
        };
    }
}
