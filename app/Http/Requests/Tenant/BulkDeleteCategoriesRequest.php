<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of product categories.
 */
class BulkDeleteCategoriesRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:categories,id';
    }
}
