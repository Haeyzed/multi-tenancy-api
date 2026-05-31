<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\Payment;

/**
 * Payment instrument category recorded on a payment transaction.
 *
 * Stored on {@see Payment::$payment_method_type} in the `payments` table.
 */
enum PaymentMethodType: string implements HasLabel
{
    use InteractsWithEnum;

    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Wallet = 'wallet';
    case Crypto = 'crypto';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::BankTransfer => 'Bank Transfer',
            self::Wallet => 'Wallet',
            self::Crypto => 'Crypto',
        };
    }
}
