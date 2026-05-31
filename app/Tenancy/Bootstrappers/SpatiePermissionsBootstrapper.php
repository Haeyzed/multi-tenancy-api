<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * Keeps Spatie permission models and cache isolated between central and tenant contexts.
 */
class SpatiePermissionsBootstrapper implements TenancyBootstrapper
{
    public function __construct(
        protected PermissionRegistrar $registrar,
    ) {}

    public function bootstrap(Tenant $tenant): void
    {
        $this->registrar->clearPermissionsCollection();
        $this->registrar->setPermissionClass(config('permission.tenant_models.permission'));
        $this->registrar->setRoleClass(config('permission.tenant_models.role'));
        $this->registrar->cacheKey = 'spatie.permission.cache.tenant.'.$tenant->getTenantKey();
    }

    public function revert(): void
    {
        $this->registrar->clearPermissionsCollection();
        $this->registrar->setPermissionClass(config('permission.models.permission'));
        $this->registrar->setRoleClass(config('permission.models.role'));
        $this->registrar->cacheKey = config('permission.cache.key');
    }
}
