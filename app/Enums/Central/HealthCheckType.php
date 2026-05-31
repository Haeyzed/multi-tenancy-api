<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\TenantHealthCheck;

/**
 * Infrastructure component evaluated during a tenant health check.
 *
 * Stored on {@see TenantHealthCheck::$check_type} in the `tenant_health_checks` table.
 */
enum HealthCheckType: string implements HasLabel
{
    use InteractsWithEnum;

    case DbConnectivity = 'db_connectivity';
    case Storage = 'storage';
    case Queue = 'queue';
    case Ssl = 'ssl';
    case Redis = 'redis';
    case Search = 'search';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::DbConnectivity => 'Database Connectivity',
            self::Storage => 'Storage',
            self::Queue => 'Queue',
            self::Ssl => 'SSL',
            self::Redis => 'Redis',
            self::Search => 'Search',
        };
    }
}
