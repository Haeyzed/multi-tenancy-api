<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of store staff users.
 */
class BulkDeleteUsersRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'uuid|exists:users,id';
    }
}
