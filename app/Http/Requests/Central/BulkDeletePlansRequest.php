<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates bulk deletion of subscription plans.
 */
class BulkDeletePlansRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:plans,id';
    }
}
