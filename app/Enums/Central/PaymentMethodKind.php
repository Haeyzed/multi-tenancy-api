<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\PaymentMethod;

/**
 * Stored payment method category for a tenant.
 *
 * Stored on {@see PaymentMethod::$type} in the `payment_methods` table.
 */
enum PaymentMethodKind: string implements HasLabel
{
    use InteractsWithEnum;

    case Card = 'card';
    case BankAccount = 'bank_account';
    case Wallet = 'wallet';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::BankAccount => 'Bank Account',
            self::Wallet => 'Wallet',
        };
    }
}
