<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * WebSocket (Reverb) connection settings for authenticated central clients.
 */
class BroadcastingConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $connection = config('broadcasting.connections.reverb');
        $options = $connection['options'] ?? [];

        return $this->success([
            'driver' => 'reverb',
            'key' => $connection['key'] ?? null,
            'host' => $options['host'] ?? 'localhost',
            'port' => (int)($options['port'] ?? 8080),
            'scheme' => $options['scheme'] ?? 'http',
            'auth_endpoint' => url('/api/central/broadcasting/auth'),
            'channel' => 'central.tenants',
        ]);
    }
}
