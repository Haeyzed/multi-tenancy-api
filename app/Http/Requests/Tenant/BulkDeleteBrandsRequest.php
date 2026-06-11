<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of product brands.
 */
class BulkDeleteBrandsRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:brands,id';
    }
}
