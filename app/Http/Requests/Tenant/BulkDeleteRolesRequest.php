<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of store Spatie roles.
 */
class BulkDeleteRolesRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:roles,id';
    }
}
