<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Data\Central\TenantOwnerProvisioningResult;
use App\Enums\Tenant\TenantUserRole;
use App\Models\Central\Tenant;
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use App\Services\Tenant\TenantBootstrapService;
use App\Support\TenantUrl;
use Database\Seeders\Tenant\TenantRolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Create the tenant owner user and baseline store configuration.
 */
class TenantOwnerProvisioningService
{
    public function __construct(
        private readonly TenantBootstrapService $bootstrapService,
    ) {}

    /**
     * Provision the tenant owner inside the tenant database.
     */
    public function provision(Tenant $tenant, ?string $ownerPassword = null): TenantOwnerProvisioningResult
    {
        /** @var TenantOwnerProvisioningResult $result */
        $result = $tenant->run(function () use ($tenant, $ownerPassword) {
            $this->ensureRolesAndPermissions();
            $this->bootstrapService->bootstrap($tenant);

            $existingOwner = User::query()
                ->where('email', $tenant->owner_email)
                ->first();

            if ($existingOwner !== null) {
                return TenantOwnerProvisioningResult::alreadyProvisioned($existingOwner);
            }

            $passwordResolution = $this->resolveOwnerPassword($tenant, $ownerPassword);

            [$firstName, $lastName] = $this->resolveOwnerNames($tenant);

            $user = User::query()->create([
                'email' => $tenant->owner_email,
                'password' => $passwordResolution['password'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            $ownerRole = Role::query()
                ->where('name', TenantUserRole::StoreOwner->value)
                ->where('guard_name', TenantUserRole::GUARD)
                ->firstOrFail();

            $user->assignRole($ownerRole);

            $passwordSetupUrl = null;

            if ($passwordResolution['requires_setup']) {
                $token = Password::broker('tenant_users')->createToken($user);
                $passwordSetupUrl = TenantUrl::ownerPasswordSetup($tenant, $token, $user->email);
            }

            return new TenantOwnerProvisioningResult(
                user: $user,
                created: true,
                requiresPasswordSetup: $passwordResolution['requires_setup'],
                passwordSetupUrl: $passwordSetupUrl,
            );
        });

        $this->persistCentralMetadata($tenant, $result);

        return $result;
    }

    private function ensureRolesAndPermissions(): void
    {
        if (Role::query()->where('name', TenantUserRole::StoreOwner->value)->exists()) {
            return;
        }

        (new TenantRolePermissionSeeder)->run();
    }

    /**
     * @return array{password: string, requires_setup: bool}
     */
    private function resolveOwnerPassword(Tenant $tenant, ?string $explicitPassword): array
    {
        if ($explicitPassword !== null && $explicitPassword !== '') {
            return [
                'password' => Hash::isHashed($explicitPassword)
                    ? $explicitPassword
                    : Hash::make($explicitPassword),
                'requires_setup' => false,
            ];
        }

        $hashedPassword = $tenant->meta['pending_owner_password_hash'] ?? null;

        if (is_string($hashedPassword) && $hashedPassword !== '' && Hash::isHashed($hashedPassword)) {
            return [
                'password' => $hashedPassword,
                'requires_setup' => false,
            ];
        }

        return [
            'password' => Hash::make(Str::password(16)),
            'requires_setup' => true,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveOwnerNames(Tenant $tenant): array
    {
        $meta = $tenant->meta ?? [];
        $firstName = trim((string) ($meta['owner_first_name'] ?? ''));
        $lastName = trim((string) ($meta['owner_last_name'] ?? ''));

        if ($firstName !== '') {
            return [$firstName, $lastName];
        }

        return $this->parseOwnerName($tenant->owner_name);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseOwnerName(string $ownerName): array
    {
        $parts = preg_split('/\s+/', trim($ownerName), 2) ?: [];

        return [
            $parts[0] ?? 'Owner',
            $parts[1] ?? '',
        ];
    }

    private function persistCentralMetadata(
        Tenant                        $tenant,
        TenantOwnerProvisioningResult $result,
    ): void
    {
        $meta = $tenant->meta ?? [];

        unset($meta['pending_owner_password'], $meta['pending_owner_password_hash']);

        $meta['owner_user_id'] = $result->user->id;
        $meta['owner_provisioned_at'] = now()->toIso8601String();
        $meta['owner_requires_password_setup'] = $result->requiresPasswordSetup;
        $meta['owner_login_url'] = TenantUrl::ownerLogin($tenant);

        if ($result->passwordSetupUrl !== null) {
            $meta['owner_password_setup_url'] = $result->passwordSetupUrl;
        } else {
            unset($meta['owner_password_setup_url']);
        }

        $tenant->forceFill(['meta' => $meta])->save();
    }
}
