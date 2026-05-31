<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\TenantSupportMessage;

/**
 * Author type of a support ticket message.
 *
 * Stored on {@see TenantSupportMessage::$sender_type} in the `tenant_support_messages` table.
 */
enum MessageSenderType: string implements HasLabel
{
    use InteractsWithEnum;

    case User = 'user';
    case System = 'system';
    case Admin = 'admin';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::System => 'System',
            self::Admin => 'Admin',
        };
    }
}
