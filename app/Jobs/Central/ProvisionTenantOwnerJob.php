<?php

declare(strict_types=1);

namespace App\Jobs\Central;

use App\Models\Central\Tenant;
use App\Services\Central\TenantOwnerProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Provision the tenant owner after the tenant database is ready.
 */
class ProvisionTenantOwnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
    ) {}

    public function handle(TenantOwnerProvisioningService $provisioning): void
    {
        $tenant = $this->tenant->fresh();

        if ($tenant === null) {
            return;
        }

        $meta = $tenant->meta ?? [];

        if (! empty($meta['owner_user_id'])) {
            return;
        }

        $provisioning->provision($tenant);
    }
}
