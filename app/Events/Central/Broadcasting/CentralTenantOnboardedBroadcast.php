<?php

declare(strict_types=1);

namespace App\Events\Central\Broadcasting;

use App\Http\Resources\Central\TenantResource;
use App\Models\Central\Tenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a tenant finishes onboarding (owner provisioned / payment verified).
 */
class CentralTenantOnboardedBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Tenant $tenant,
    )
    {
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('central.tenants'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'tenant.onboarded';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->tenant->loadMissing(['plan', 'domains', 'activeSubscription']);

        return [
            'event' => 'tenant.onboarded',
            'tenant' => (new TenantResource($this->tenant))->resolve(),
        ];
    }
}
