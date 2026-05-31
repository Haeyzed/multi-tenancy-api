<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Central platform authentication.
 */
class AuthService
{
    /**
     * Authenticate a user and issue an API token.
     *
     * @param  string  $email  User email address.
     * @param  string  $password  Plain-text password.
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive.'],
            ]);
        }

        $user->query()->update(['last_login_at' => now()]);

        return [
            'user' => $user->fresh(),
            'token' => $user->createToken('central-api')->plainTextToken,
        ];
    }

    /**
     * Revoke the current access token for the authenticated user.
     *
     * @param  User  $user  Authenticated user.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Get the authenticated user.
     *
     * @param  User  $user  Authenticated user.
     */
    public function me(User $user): User
    {
        return $user;
    }
}
