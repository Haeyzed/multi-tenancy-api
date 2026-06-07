<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates bulk deletion of Spatie roles.
 */
class BulkDeleteRolesRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:roles,id';
    }
}
