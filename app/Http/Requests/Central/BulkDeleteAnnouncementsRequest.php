<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates bulk deletion of platform announcements.
 */
class BulkDeleteAnnouncementsRequest extends BulkDeleteRequest
{
    protected function idRule(): string
    {
        return 'integer|exists:platform_announcements,id';
    }
}
