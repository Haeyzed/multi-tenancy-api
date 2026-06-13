<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates bulk deletion of tenant subscriptions.
 */
class BulkDeleteSubscriptionsRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:subscriptions,id';
    }
}
