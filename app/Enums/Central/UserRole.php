<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\User;

/**
 * Platform administrator roles for central user accounts.
 *
 * Stored on {@see User::$role} in the `users` table.
 */
enum UserRole: string implements HasLabel
{
    use InteractsWithEnum;

    case SuperAdmin = 'super_admin';
    case Support = 'support';
    case Billing = 'billing';
    case Technical = 'technical';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Support => 'Support',
            self::Billing => 'Billing',
            self::Technical => 'Technical',
        };
    }
}
