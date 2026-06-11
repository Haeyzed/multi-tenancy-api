<?php

declare(strict_types=1);

namespace App\Listeners\Central;

use App\Events\Central\Broadcasting\CentralTenantOnboardedBroadcast;
use App\Events\Central\TenantOnboarded;
use App\Support\SafeBroadcast;

/**
 * Push tenant onboarding updates to central admin clients via Reverb.
 */
class BroadcastCentralTenantOnboarded
{
    public function handle(TenantOnboarded $event): void
    {
        SafeBroadcast::dispatch(new CentralTenantOnboardedBroadcast(
            $event->tenant->fresh(['plan', 'domains', 'activeSubscription']),
        ));
    }
}
