<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\ErrorLogSeverity;
use App\Models\Concerns\FilterableByTenant;
use App\Support\QueryFilter;
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
     * Scope a query to search by message or channel.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                    ->orWhere('channel', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Filter by severity values.
     *
     * @param list<string> $values
     */
    public function scopeFilterSeverity(Builder $query, array $values): void
    {
        $query->when($values !== [], fn(Builder $q) => $q->whereIn('severity', $values));
    }

    /**
     * Filter by resolved/unresolved tokens.
     *
     * @param list<string> $values
     */
    public function scopeFilterResolution(Builder $query, array $values): void
    {
        $mapped = QueryFilter::booleanResolved($values);

        if ($mapped === []) {
            return;
        }

        $query->where(function (Builder $q) use ($mapped) {
            foreach ($mapped as $resolved) {
                if ($resolved) {
                    $q->orWhereNotNull('resolved_at');
                } else {
                    $q->orWhereNull('resolved_at');
                }
            }
        });
    }

    /**
     * Tenant associated with this error, if any.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

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
}
