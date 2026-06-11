<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of warehouses.
 */
class BulkDeleteWarehousesRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'uuid|exists:warehouses,id';
    }
}
