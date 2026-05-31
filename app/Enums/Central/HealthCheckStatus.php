<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\TenantHealthCheck;

/**
 * Result severity of a tenant health check.
 *
 * Stored on {@see TenantHealthCheck::$status} in the `tenant_health_checks` table.
 */
enum HealthCheckStatus: string implements HasLabel
{
    use InteractsWithEnum;

    case Healthy = 'healthy';
    case Warning = 'warning';
    case Critical = 'critical';
    case Unknown = 'unknown';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::Warning => 'Warning',
            self::Critical => 'Critical',
            self::Unknown => 'Unknown',
        };
    }
}
