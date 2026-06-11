<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Central\Tenant;

/**
 * Build tenant-facing URLs from central tenant records.
 */
final class TenantUrl
{
    public static function ownerLogin(Tenant $tenant): string
    {
        return self::format(config('tenancy.owner_login_url', 'https://{domain}/admin/login'), $tenant);
    }

    private static function format(string $template, Tenant $tenant): string
    {
        $domain = $tenant->domain;

        if ($domain === null || $domain === '') {
            $domain = $tenant->domains()->where('is_primary', true)->value('domain')
                ?? $tenant->domains()->value('domain')
                ?? 'localhost';
        }

        return str_replace('{domain}', $domain, $template);
    }

    public static function ownerPasswordSetup(Tenant $tenant, string $token, string $email): string
    {
        $base = self::format(
            config('tenancy.owner_password_setup_url', 'https://{domain}/admin/set-password'),
            $tenant,
        );

        return $base . '?' . http_build_query([
                'token' => $token,
                'email' => $email,
            ]);
    }
}
