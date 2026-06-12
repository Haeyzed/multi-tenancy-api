<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * Point auth resolution at the tenant Sanctum guard while tenancy is active.
 */
class AuthTenancyBootstrapper implements TenancyBootstrapper
{
    private ?string $previousDefaultGuard = null;

    public function bootstrap(Tenant $tenant): void
    {
        $this->previousDefaultGuard = config('auth.defaults.guard');
        config(['auth.defaults.guard' => 'tenant']);
        Auth::shouldUse('tenant');
    }

    public function revert(): void
    {
        config(['auth.defaults.guard' => $this->previousDefaultGuard ?? 'web']);
        Auth::shouldUse($this->previousDefaultGuard ?? 'web');
        $this->previousDefaultGuard = null;
    }
}
