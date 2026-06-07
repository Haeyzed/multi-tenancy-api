<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates bulk deletion of platform tenants.
 */
class BulkDeleteTenantsRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'uuid|exists:tenants,id';
    }
}
