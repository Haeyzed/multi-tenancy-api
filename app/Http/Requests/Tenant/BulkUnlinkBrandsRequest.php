<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk unlink of product brands.
 */
class BulkUnlinkBrandsRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:brands,id';
    }
}
