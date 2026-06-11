<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Data\Central\TenantOwnerProvisioningResult;
use App\Enums\Tenant\TenantUserRole;
use App\Models\Central\Tenant;
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use App\Support\TenantUrl;
use Database\Seeders\Tenant\TenantRolePermissionSeeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Create the tenant owner user and baseline store configuration.
 */
class TenantOwnerProvisioningService
{
    /**
     * Provision the tenant owner inside the tenant database.
     */
    public function provision(Tenant $tenant, ?string $ownerPassword = null): TenantOwnerProvisioningResult
    {
        /** @var TenantOwnerProvisioningResult $result */
        $result = $tenant->run(function () use ($tenant, $ownerPassword) {
            $this->ensureRolesAndPermissions();

            $existingOwner = User::query()
                ->where('email', $tenant->owner_email)
                ->first();

            if ($existingOwner !== null) {
                return TenantOwnerProvisioningResult::alreadyProvisioned($existingOwner);
            }

            $this->ensureStoreSettings($tenant);

            $passwordResolution = $this->resolveOwnerPassword($tenant, $ownerPassword);

            [$firstName, $lastName] = $this->parseOwnerName($tenant->owner_name);

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

    private function ensureStoreSettings(Tenant $tenant): void
    {
        if (DB::table('store_settings')->exists()) {
            return;
        }

        DB::table('store_settings')->insert([
            'store_name' => $tenant->name,
            'store_slug' => $tenant->slug,
            'default_language' => $tenant->settings['locale'] ?? 'en',
            'timezone' => $tenant->settings['timezone'] ?? 'UTC',
            'currency' => $tenant->settings['currency'] ?? 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array{password: string, requires_setup: bool}
     */
    private function resolveOwnerPassword(Tenant $tenant, ?string $explicitPassword): array
    {
        if ($explicitPassword !== null && $explicitPassword !== '') {
            return [
                'password' => $explicitPassword,
                'requires_setup' => false,
            ];
        }

        $encrypted = $tenant->meta['pending_owner_password'] ?? null;

        if (is_string($encrypted) && $encrypted !== '') {
            try {
                return [
                    'password' => Crypt::decryptString($encrypted),
                    'requires_setup' => false,
                ];
            } catch (RuntimeException) {
                // Fall through to generated setup flow.
            }
        }

        return [
            'password' => Str::password(16),
            'requires_setup' => true,
        ];
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
        Tenant $tenant,
        TenantOwnerProvisioningResult $result,
    ): void {
        $meta = $tenant->meta ?? [];

        unset($meta['pending_owner_password']);

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
