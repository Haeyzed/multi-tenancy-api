<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk unlink of product categories.
 */
class BulkUnlinkCategoriesRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:categories,id';
    }
}
