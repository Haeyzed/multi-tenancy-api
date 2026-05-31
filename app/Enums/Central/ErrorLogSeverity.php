<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\ErrorLog;

/**
 * Severity level of a platform or tenant error log entry.
 *
 * Stored on {@see ErrorLog::$severity} in the `error_logs` table.
 */
enum ErrorLogSeverity: string implements HasLabel
{
    use InteractsWithEnum;

    case Debug = 'debug';
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
    case Critical = 'critical';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Debug => 'Debug',
            self::Info => 'Info',
            self::Warning => 'Warning',
            self::Error => 'Error',
            self::Critical => 'Critical',
        };
    }
}
