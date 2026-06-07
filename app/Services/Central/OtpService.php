<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\OtpPurpose;
use App\Models\Central\Otp;
use App\Models\Central\User;
use App\Notifications\Central\AuthOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Random\RandomException;

/**
 * Issues, verifies, and resends authentication OTPs.
 */
class OtpService
{
    /**
     * Create a new OTP and send it to the user.
     *
     * @throws RandomException
     */
    public function issue(User $user, OtpPurpose $purpose): void
    {
        $this->invalidatePending($user->email, $purpose);

        [$plainOtp, $record] = $this->createRecord($user, $purpose);

        $user->notify(new AuthOtpNotification(
            $plainOtp,
            $purpose,
            (int) config('otp.ttl_minutes'),
        ));
    }

    /**
     * Resend the latest pending OTP or issue a new one.
     *
     * @throws RandomException
     */
    public function resend(string $email, OtpPurpose $purpose): void
    {
        $user = $this->findActiveUserByEmail($email);

        if (! $user) {
            return;
        }

        $record = $this->findLatestPending($email, $purpose);

        if ($record !== null) {
            $this->assertResendAllowed($record);

            $record->increment('resend_count');
            $record->update(['last_sent_at' => now()]);
            $plainOtp = $this->regenerateOtp($record);
        } else {
            $this->issue($user, $purpose);

            return;
        }

        $user->notify(new AuthOtpNotification(
            $plainOtp,
            $purpose,
            (int) config('otp.ttl_minutes'),
        ));
    }

    /**
     * Verify an OTP and return a short-lived verification token.
     *
     * @return array{verification_token: string, expires_in_minutes: int}
     */
    public function verify(string $email, string $otp, OtpPurpose $purpose): array
    {
        $record = $this->findLatestPending($email, $purpose);

        if ($record === null) {
            throw ValidationException::withMessages([
                'otp' => ['This verification code is invalid or has expired.'],
            ]);
        }

        if ($record->isExpired()) {
            $record->delete();

            throw ValidationException::withMessages([
                'otp' => ['This verification code has expired.'],
            ]);
        }

        if (! Hash::check($otp, $record->otp)) {
            $record->increment('attempts');

            if ($record->attempts >= (int) config('otp.max_attempts')) {
                $record->delete();
            }

            throw ValidationException::withMessages([
                'otp' => ['The verification code is incorrect.'],
            ]);
        }

        $verificationToken = Str::random(64);
        $tokenTtlMinutes = (int) config('otp.verification_token_ttl_minutes');

        $record->update([
            'verified_at' => now(),
            'verification_token' => Hash::make($verificationToken),
            'verification_token_expires_at' => now()->addMinutes($tokenTtlMinutes),
        ]);

        return [
            'verification_token' => $verificationToken,
            'expires_in_minutes' => $tokenTtlMinutes,
        ];
    }

    /**
     * Validate a verification token for a completed OTP flow.
     */
    public function consumeVerificationToken(
        string $email,
        string $verificationToken,
        OtpPurpose $purpose,
    ): Otp {
        $record = Otp::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNotNull('verified_at')
            ->latest('id')
            ->first();

        if ($record === null || ! $record->hasValidVerificationToken()) {
            throw ValidationException::withMessages([
                'verification_token' => ['The verification token is invalid or has expired.'],
            ]);
        }

        if (! Hash::check($verificationToken, (string) $record->verification_token)) {
            throw ValidationException::withMessages([
                'verification_token' => ['The verification token is invalid or has expired.'],
            ]);
        }

        return $record;
    }

    /**
     * Remove a consumed OTP record.
     */
    public function delete(Otp $record): void
    {
        $record->delete();
    }

    private function findActiveUserByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();
    }

    private function invalidatePending(string $email, OtpPurpose $purpose): void
    {
        Otp::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();
    }

    private function findLatestPending(string $email, OtpPurpose $purpose): ?Otp
    {
        return Otp::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();
    }

    /**
     * @return array{0: string, 1: Otp}
     *
     * @throws RandomException
     */
    private function createRecord(User $user, OtpPurpose $purpose): array
    {
        $plainOtp = $this->generatePlainOtp();
        $ttlMinutes = (int) config('otp.ttl_minutes');

        $record = Otp::query()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => Hash::make($plainOtp),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes($ttlMinutes),
            'last_sent_at' => now(),
        ]);

        return [$plainOtp, $record];
    }

    /**
     * @throws RandomException
     */
    private function regenerateOtp(Otp $record): string
    {
        $plainOtp = $this->generatePlainOtp();
        $ttlMinutes = (int) config('otp.ttl_minutes');

        $record->update([
            'otp' => Hash::make($plainOtp),
            'expires_at' => now()->addMinutes($ttlMinutes),
            'attempts' => 0,
        ]);

        return $plainOtp;
    }

    /**
     * @throws RandomException
     */
    private function generatePlainOtp(): string
    {
        $length = max(4, (int) config('otp.length'));

        return str_pad(
            (string) random_int(0, (10 ** $length) - 1),
            $length,
            '0',
            STR_PAD_LEFT,
        );
    }

    private function assertResendAllowed(Otp $record): void
    {
        if ($record->last_sent_at === null) {
            return;
        }

        $cooldownSeconds = (int) config('otp.resend_cooldown_seconds');
        $nextAllowedAt = $record->last_sent_at->copy()->addSeconds($cooldownSeconds);

        if ($nextAllowedAt->isFuture()) {
            $waitSeconds = max(1, (int) now()->diffInSeconds($nextAllowedAt));

            throw ValidationException::withMessages([
                'email' => ["Please wait $waitSeconds seconds before requesting another code."],
            ]);
        }
    }
}
