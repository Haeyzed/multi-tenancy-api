<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\ErrorLogSeverity;
use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Platform or tenant-scoped error log entry.
 *
 * @property int $id
 * @property string|null $tenant_id
 * @property ErrorLogSeverity $severity
 * @property string $channel
 * @property string $message
 * @property array<string, mixed>|null $context
 * @property Carbon $occurred_at
 * @property Carbon|null $resolved_at
 *
 * @method static Builder|ErrorLog forTenant(?string $tenantId = null)
 * @method static Builder|ErrorLog search(?string $search)
 */
class ErrorLog extends Model
{
    use FilterableByTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'severity',
        'channel',
        'message',
        'context',
        'occurred_at',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity' => ErrorLogSeverity::class,
            'context' => 'array',
            'occurred_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to search by message or channel.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                    ->orWhere('channel', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Tenant associated with this error, if any.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
