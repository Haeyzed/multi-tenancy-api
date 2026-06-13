<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of catalog products.
 */
class BulkDeleteProductsRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:products,id';
    }
}
