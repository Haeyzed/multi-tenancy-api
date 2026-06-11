<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of store addresses.
 */
class BulkDeleteStoreAddressesRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:store_addresses,id';
    }
}
