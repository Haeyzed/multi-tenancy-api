<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of stores.
 */
class BulkDeleteStoresRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'uuid|exists:stores,id';
    }
}
