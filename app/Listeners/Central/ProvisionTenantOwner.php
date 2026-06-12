<?php

declare(strict_types=1);

namespace App\Listeners\Central;

use App\Events\Central\TenantOnboarded;
use App\Services\Central\TenantOwnerProvisioningService;

/**
 * Create the tenant owner account when onboarding completes.
 */
class ProvisionTenantOwner
{
    public function __construct(
        private readonly TenantOwnerProvisioningService $provisioning,
    ) {}

    public function handle(TenantOnboarded $event): void
    {
        $this->provisioning->provision($event->tenant, $event->ownerPassword);
    }
}
