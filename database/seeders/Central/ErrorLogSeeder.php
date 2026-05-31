<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\ErrorLogSeverity;
use App\Models\Central\ErrorLog;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed sample platform and tenant error logs.
 */
class ErrorLogSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logs = [
            [
                'tenant' => 'acme-corp',
                'channel' => 'app',
                'message' => 'Slow query detected: SELECT * FROM products WHERE tenant_id = ? took 2.3s',
                'severity' => ErrorLogSeverity::Warning,
                'context' => ['query' => 'SELECT * FROM products WHERE tenant_id = ?', 'time_ms' => 2300],
                'occurred_at' => now()->subHours(2),
                'resolved_at' => now()->subHours(1),
            ],
            [
                'tenant' => 'beta-solutions',
                'channel' => 'queue',
                'message' => 'Job App\\Jobs\\ProcessOrder failed after 3 attempts: Connection refused',
                'severity' => ErrorLogSeverity::Error,
                'context' => ['job_id' => 12345, 'exception' => 'Connection refused'],
                'occurred_at' => now()->subHours(5),
                'resolved_at' => null,
            ],
            [
                'tenant' => 'delta-works',
                'channel' => 'database',
                'message' => 'Database connection lost for tenant database tenant_delta_works',
                'severity' => ErrorLogSeverity::Critical,
                'context' => ['database' => 'tenant_delta_works', 'error' => 'Connection timeout'],
                'occurred_at' => now()->subMinutes(30),
                'resolved_at' => null,
            ],
            [
                'tenant' => null,
                'channel' => 'system',
                'message' => 'Platform backup completed successfully',
                'severity' => ErrorLogSeverity::Info,
                'context' => ['backup_size' => '2.4GB', 'duration' => '15m'],
                'occurred_at' => now()->subHours(12),
                'resolved_at' => now()->subHours(12),
            ],
            [
                'tenant' => 'acme-corp',
                'channel' => 'api',
                'message' => 'API request received: GET /api/v1/products',
                'severity' => ErrorLogSeverity::Debug,
                'context' => ['method' => 'GET', 'endpoint' => '/api/v1/products', 'ip' => '192.168.1.1'],
                'occurred_at' => now()->subMinutes(15),
                'resolved_at' => null,
            ],
        ];

        foreach ($logs as $log) {
            ErrorLog::query()->updateOrCreate(
                [
                    'tenant_id' => $log['tenant'] ? $this->tenant($log['tenant'])->id : null,
                    'channel' => $log['channel'],
                    'message' => $log['message'],
                ],
                [
                    'severity' => $log['severity'],
                    'context' => $log['context'],
                    'occurred_at' => $log['occurred_at'],
                    'resolved_at' => $log['resolved_at'],
                ],
            );
        }
    }
}
