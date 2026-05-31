<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\HealthCheckStatus;
use App\Enums\Central\HealthCheckType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Health check result recorded for a tenant environment.
 *
 * @property int $id
 * @property string $tenant_id
 * @property HealthCheckType $check_type
 * @property HealthCheckStatus $status
 * @property int|null $response_time_ms
 * @property string|null $message
 * @property Carbon $checked_at
 */
class TenantHealthCheck extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'check_type',
        'status',
        'response_time_ms',
        'message',
        'checked_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_type' => HealthCheckType::class,
            'status' => HealthCheckStatus::class,
            'checked_at' => 'datetime',
        ];
    }

    /**
     * Tenant this health check was executed for.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
