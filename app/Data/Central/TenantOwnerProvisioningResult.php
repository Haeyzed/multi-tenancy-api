<?php

declare(strict_types=1);

namespace App\Data\Central;

use App\Models\Tenant\User;

/**
 * Result of provisioning the tenant owner account.
 */
final readonly class TenantOwnerProvisioningResult
{
    public function __construct(
        public User    $user,
        public bool    $created,
        public bool    $requiresPasswordSetup,
        public ?string $passwordSetupUrl = null,
    )
    {
    }

    public static function alreadyProvisioned(User $user): self
    {
        return new self(
            user: $user,
            created: false,
            requiresPasswordSetup: false,
        );
    }
}
