<?php

declare(strict_types=1);

namespace App\Data\Payment;

/**
 * Result of creating a hosted checkout session.
 */
readonly class CheckoutResult
{
    public function __construct(
        public string $checkoutUrl,
        public string $reference,
    ) {}
}
