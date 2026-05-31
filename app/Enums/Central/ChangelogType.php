<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\PlatformChangelog;

/**
 * Category of a published platform changelog entry.
 *
 * Stored on {@see PlatformChangelog::$type} in the `platform_changelog` table.
 */
enum ChangelogType: string implements HasLabel
{
    use InteractsWithEnum;

    case Feature = 'feature';
    case Fix = 'fix';
    case Breaking = 'breaking';
    case Security = 'security';
    case Performance = 'performance';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Feature => 'Feature',
            self::Fix => 'Fix',
            self::Breaking => 'Breaking',
            self::Security => 'Security',
            self::Performance => 'Performance',
        };
    }
}
