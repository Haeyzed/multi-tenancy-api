<?php

declare(strict_types=1);

namespace App\Events\Central;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantOnboarded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public ?string $ownerPassword = null,
    ) {}
}
