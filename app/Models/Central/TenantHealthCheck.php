<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\HealthCheckStatus;
use App\Enums\Central\HealthCheckType;
use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
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
 *
 * @method static Builder|TenantHealthCheck forTenant(?string $tenantId = null)
 * @method static Builder|TenantHealthCheck search(?string $search)
 */
class TenantHealthCheck extends Model
{
    use FilterableByTenant, HasFactory;

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
     * Scope a query to search by message, check type, or status.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                    ->orWhere('check_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Tenant this health check was executed for.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
