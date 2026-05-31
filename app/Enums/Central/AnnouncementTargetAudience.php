<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\PlatformAnnouncement;

/**
 * Audience scope for a platform announcement.
 *
 * Stored on {@see PlatformAnnouncement::$target_audience} in the `platform_announcements` table.
 */
enum AnnouncementTargetAudience: string implements HasLabel
{
    use InteractsWithEnum;

    case All = 'all';
    case PlanSpecific = 'plan_specific';
    case AdminsOnly = 'admins_only';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::PlanSpecific => 'Plan Specific',
            self::AdminsOnly => 'Admins Only',
        };
    }
}
