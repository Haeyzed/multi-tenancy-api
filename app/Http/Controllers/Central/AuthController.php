<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Enums\Central\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\ChangePasswordRequest;
use App\Http\Requests\Central\ForgotPasswordRequest;
use App\Http\Requests\Central\LoginRequest;
use App\Http\Requests\Central\ResendOtpRequest;
use App\Http\Requests\Central\ResetPasswordRequest;
use App\Http\Requests\Central\VerifyOtpRequest;
use App\Http\Resources\Central\UserResource;
use App\Models\Central\User;
use App\Services\Central\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Random\RandomException;

/**
 * Central platform authentication.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $service,
    ) {}

    /**
     * Authenticate and issue an API token.
     *
     * @param  LoginRequest  $request  Validated login credentials.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->login(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful.');
    }

    /**
     * Revoke the current API token.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->service->logout($user);

        return $this->deleted('Logged out successfully.');
    }

    /**
     * Get the authenticated user profile.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(new UserResource($this->service->me($user)), 'Profile retrieved successfully.');
    }

    /**
     * Send a password-reset OTP to the given email address.
     *
     * @param  ForgotPasswordRequest  $request  Validated email address.
     *
     * @throws RandomException
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->service->forgotPassword($request->string('email')->toString());

        return $this->success(message: 'If an account exists for that email, a verification code has been sent.');
    }

    /**
     * Resend an OTP for the given email and purpose.
     *
     * @param  ResendOtpRequest  $request  Validated resend payload.
     *
     * @throws RandomException
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $this->service->resendOtp(
            $request->string('email')->toString(),
            $request->enum('purpose', OtpPurpose::class),
        );

        return $this->success(message: 'If an account exists for that email, a verification code has been sent.');
    }

    /**
     * Verify an OTP and issue a short-lived verification token.
     *
     * @param  VerifyOtpRequest  $request  Validated verification payload.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->service->verifyOtp(
            $request->string('email')->toString(),
            $request->string('otp')->toString(),
            $request->enum('purpose', OtpPurpose::class),
        );

        return $this->success($result, 'Verification successful.');
    }

    /**
     * Reset the account password using a verified OTP token.
     *
     * @param  ResetPasswordRequest  $request  Validated reset payload.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->service->resetPassword(
            $request->string('email')->toString(),
            $request->string('verification_token')->toString(),
            $request->string('password')->toString(),
        );

        return $this->success(message: 'Password reset successfully.');
    }

    /**
     * Send an OTP to confirm a password change for the authenticated user.
     *
     * @param  Request  $request  Incoming HTTP request.
     *
     * @throws RandomException
     */
    public function requestPasswordChangeOtp(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->service->requestPasswordChangeOtp($user);

        return $this->success(message: 'A verification code has been sent to your email.');
    }

    /**
     * Change the authenticated user's password.
     *
     * @param  ChangePasswordRequest  $request  Validated change payload.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->service->changePassword(
            $user,
            $request->string('password')->toString(),
            $request->filled('current_password')
                ? $request->string('current_password')->toString()
                : null,
            $request->filled('verification_token')
                ? $request->string('verification_token')->toString()
                : null,
        );

        return $this->success(message: 'Password changed successfully.');
    }
}
