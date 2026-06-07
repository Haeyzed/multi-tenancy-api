<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;

/**
 * Reason an authentication OTP was issued.
 */
enum OtpPurpose: string implements HasLabel
{
    use InteractsWithEnum;

    case PasswordReset = 'password_reset';
    case PasswordChange = 'password_change';
    case EmailVerification = 'email_verification';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::PasswordReset => 'Password reset',
            self::PasswordChange => 'Password change',
            self::EmailVerification => 'Email verification',
        };
    }
}
