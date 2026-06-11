<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\OtpPurpose;
use App\Models\Central\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Random\RandomException;

/**
 * Central platform authentication.
 */
readonly class AuthService
{
    public function __construct(
        private OtpService $otpService,
    )
    {
    }

    /**
     * Authenticate a user and issue an API token.
     *
     * @param string $email User email address.
     * @param string $password Plain-text password.
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password): array
    {
        $user = User::query()->where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        return [
            'user' => $this->loadAuthorizationRelations($user->fresh()),
            'token' => $user->createToken('central-api')->plainTextToken,
        ];
    }

    /**
     * Eager-load roles and direct permissions for authorization payloads.
     */
    private function loadAuthorizationRelations(User $user): User
    {
        return $user->load(['roles.permissions', 'permissions']);
    }

    /**
     * Revoke the current access token for the authenticated user.
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Get the authenticated user.
     */
    public function me(User $user): User
    {
        return $this->loadAuthorizationRelations($user);
    }

    /**
     * Send a password-reset OTP if the account exists.
     *
     * @throws RandomException
     */
    public function forgotPassword(string $email): void
    {
        $user = User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        if ($user !== null) {
            $this->otpService->issue($user, OtpPurpose::PasswordReset);
        }
    }

    /**
     * Resend an OTP for the given email and purpose.
     *
     * @throws RandomException
     */
    public function resendOtp(string $email, OtpPurpose $purpose): void
    {
        $this->otpService->resend($email, $purpose);
    }

    /**
     * Verify an OTP and return a short-lived verification token.
     *
     * @return array{verification_token: string, expires_in_minutes: int}
     */
    public function verifyOtp(string $email, string $otp, OtpPurpose $purpose): array
    {
        $result = $this->otpService->verify($email, $otp, $purpose);

        if ($purpose === OtpPurpose::EmailVerification) {
            $user = User::query()->where('email', $email)->first();

            if ($user !== null && $user->email_verified_at === null) {
                $user->update(['email_verified_at' => now()]);
            }
        }

        return $result;
    }

    /**
     * Reset a password using a verified OTP token.
     */
    public function resetPassword(
        string $email,
        string $verificationToken,
        string $password,
    ): void
    {
        $record = $this->otpService->consumeVerificationToken(
            $email,
            $verificationToken,
            OtpPurpose::PasswordReset,
        );

        $user = User::query()->where('email', $email)->first();

        if ($user === null || !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Unable to reset password for this account.'],
            ]);
        }

        $user->update(['password' => $password]);
        $user->tokens()->delete();
        $this->otpService->delete($record);
    }

    /**
     * Send an OTP to confirm a password change for the authenticated user.
     *
     * @throws RandomException
     */
    public function requestPasswordChangeOtp(User $user): void
    {
        $this->otpService->issue($user, OtpPurpose::PasswordChange);
    }

    /**
     * Change the authenticated user's password.
     *
     * Provide either the current password or a verification token from OTP verification.
     */
    public function changePassword(
        User    $user,
        string  $password,
        ?string $currentPassword = null,
        ?string $verificationToken = null,
    ): void
    {
        if ($verificationToken !== null) {
            $record = $this->otpService->consumeVerificationToken(
                $user->email,
                $verificationToken,
                OtpPurpose::PasswordChange,
            );

            $user->update(['password' => $password]);
            $this->otpService->delete($record);

            return;
        }

        if ($currentPassword === null || !Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => $password]);
    }
}
