<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates bulk deletion of central invoices.
 */
class BulkDeleteInvoicesRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:invoices,id';
    }
}
