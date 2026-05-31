<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * Keeps Spatie activity log writes on the correct database per context.
 */
class SpatieActivitylogBootstrapper implements TenancyBootstrapper
{
    public function bootstrap(Tenant $tenant): void
    {
        config(['activitylog.activity_model' => config('activitylog.tenant_models.activity')]);
    }

    public function revert(): void
    {
        config(['activitylog.activity_model' => config('activitylog.central_models.activity')]);
    }
}
