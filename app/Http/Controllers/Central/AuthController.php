<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\LoginRequest;
use App\Http\Resources\Central\UserResource;
use App\Models\Central\User;
use App\Services\Central\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return $this->success(new UserResource($this->service->me($user)));
    }
}
