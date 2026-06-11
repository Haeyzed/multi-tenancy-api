<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

/**
 * Builds tenant hostnames for Stancl domain identification.
 *
 * Pattern: {tenant_slug}.{tenant_domain_base}
 * Example: acme-corp.multi-tenancy-api.test
 */
final class TenantDomain
{
    public static function suffix(): string
    {
        return (string) config('tenancy.tenant_domain_base');
    }

    /**
     * Turn API subdomain slug into a full host stored in domains.domain.
     */
    public static function qualify(string $domain): string
    {
        $domain = strtolower(trim($domain));

        if ($domain === '' || str_contains($domain, '.')) {
            return $domain;
        }

        return "{$domain}.".self::suffix();
    }
}
