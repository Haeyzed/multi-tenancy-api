<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk deletion of media library folders.
 */
class BulkDeleteMediaLibraryFoldersRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:media_library_folders,id';
    }
}
