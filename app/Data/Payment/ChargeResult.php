<?php

declare(strict_types=1);

namespace App\Data\Payment;

/**
 * Result of an off-session or saved-method charge attempt.
 */
readonly class ChargeResult
{
    public function __construct(
        public bool    $success,
        public ?string $reference = null,
        public ?string $failureMessage = null,
    )
    {
    }

    public static function succeeded(string $reference): self
    {
        return new self(success: true, reference: $reference);
    }

    public static function failed(string $message): self
    {
        return new self(success: false, failureMessage: $message);
    }
}
