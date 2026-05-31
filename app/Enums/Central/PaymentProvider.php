<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\Payment;
use App\Models\Central\PaymentMethod;
use App\Models\Central\Subscription;

/**
 * Third-party payment processor used for billing.
 *
 * Stored on {@see Subscription::$payment_provider}, {@see Payment::$payment_provider},
 * and {@see PaymentMethod::$provider}.
 */
enum PaymentProvider: string implements HasLabel
{
    use InteractsWithEnum;

    case Stripe = 'stripe';
    case Paddle = 'paddle';
    case Paypal = 'paypal';
    case Paystack = 'paystack';
    case Flutterwave = 'flutterwave';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::Paddle => 'Paddle',
            self::Paypal => 'PayPal',
            self::Paystack => 'Paystack',
            self::Flutterwave => 'Flutterwave',
        };
    }
}
