<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Compose onboarding audit notes for invoices, tenant meta, and subscription events.
 */
class OnboardingNotes
{
    public static function compose(?string $existing, string $line): string
    {
        $existing = trim((string)$existing);
        $line = trim($line);

        if ($existing === '') {
            return $line;
        }

        if ($line === '') {
            return $existing;
        }

        return $existing . "\n\n" . $line;
    }

    public static function signupStarted(string $planName, string $billingCycle, string $provider): string
    {
        return sprintf(
            '[%s] Self-onboarding started: %s (%s) via %s.',
            now()->toDateTimeString(),
            $planName,
            $billingCycle,
            $provider,
        );
    }

    public static function invoiceIssued(string $planName, string $billingCycle): string
    {
        return sprintf(
            '[%s] Initial subscription invoice issued for %s (%s).',
            now()->toDateTimeString(),
            $planName,
            $billingCycle,
        );
    }

    public static function cardVerified(string $provider, ?string $reference = null): string
    {
        $suffix = $reference !== null && $reference !== ''
            ? " Reference: {$reference}."
            : '';

        return sprintf(
            '[%s] Card verified via %s.%s Tenant activated.',
            now()->toDateTimeString(),
            $provider,
            $suffix,
        );
    }

    public static function invoicePaid(string $provider, string $reference): string
    {
        return sprintf(
            '[%s] Invoice paid via %s. Reference: %s. Tenant activated.',
            now()->toDateTimeString(),
            $provider,
            $reference,
        );
    }

    public static function trialVerificationPaid(
        string $provider,
        string $reference,
        int    $amountMinor,
        string $currency,
    ): string
    {
        return sprintf(
            '[%s] Trial card verification charge via %s (%s %s). Reference: %s.',
            now()->toDateTimeString(),
            $provider,
            number_format($amountMinor / 100, 2),
            strtoupper($currency),
            $reference,
        );
    }
}
