<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\PlatformAnnouncement;

/**
 * Content type of a platform announcement.
 *
 * Stored on {@see PlatformAnnouncement::$type} in the `platform_announcements` table.
 */
enum AnnouncementType: string implements HasLabel
{
    use InteractsWithEnum;

    case Maintenance = 'maintenance';
    case Feature = 'feature';
    case Alert = 'alert';
    case Info = 'info';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Maintenance => 'Maintenance',
            self::Feature => 'Feature',
            self::Alert => 'Alert',
            self::Info => 'Info',
        };
    }
}
