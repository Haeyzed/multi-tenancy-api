<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Broadcast without failing the request when Reverb is offline.
 */
final class SafeBroadcast
{
    public static function dispatch(ShouldBroadcast $event): void
    {
        try {
            broadcast($event);
        } catch (Throwable $exception) {
            Log::warning('Broadcast failed (is Reverb running?)', [
                'event' => $event::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
