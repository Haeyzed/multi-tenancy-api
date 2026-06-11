<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Enums\Tenant\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ChangePasswordRequest;
use App\Http\Requests\Tenant\ForgotPasswordRequest;
use App\Http\Requests\Tenant\LoginRequest;
use App\Http\Requests\Tenant\ResendOtpRequest;
use App\Http\Requests\Tenant\ResetPasswordRequest;
use App\Http\Requests\Tenant\VerifyOtpRequest;
use App\Http\Resources\Tenant\UserResource;
use App\Models\Tenant\User;
use App\Services\Tenant\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Random\RandomException;

/**
 * Tenant store staff authentication.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $service,
    )
    {
    }

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

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->service->logout($user);

        return $this->deleted('Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(new UserResource($this->service->me($user)), 'Profile retrieved successfully.');
    }

    /**
     * @throws RandomException
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->service->forgotPassword($request->string('email')->toString());

        return $this->success(message: 'If an account exists for that email, a verification code has been sent.');
    }

    /**
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

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->service->verifyOtp(
            $request->string('email')->toString(),
            $request->string('otp')->toString(),
            $request->enum('purpose', OtpPurpose::class),
        );

        return $this->success($result, 'Verification successful.');
    }

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
     * @throws RandomException
     */
    public function requestPasswordChangeOtp(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->service->requestPasswordChangeOtp($user);

        return $this->success(message: 'A verification code has been sent to your email.');
    }

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
