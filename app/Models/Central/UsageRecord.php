<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\UsageMetric;
use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Point-in-time usage measurement for a tenant or subscription.
 *
 * @property int $id
 * @property string $tenant_id
 * @property int|null $subscription_id
 * @property UsageMetric $metric
 * @property string $quantity
 * @property Carbon $recorded_at
 *
 * @method static Builder|UsageRecord forTenant(?string $tenantId = null)
 * @method static Builder|UsageRecord search(?string $search)
 * @method static Builder|UsageRecord filterMetric(array $values)
 */
class UsageRecord extends Model
{
    use FilterableByTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'metric',
        'quantity',
        'recorded_at',
    ];

    /**
     * Scope a query to search by metric or subscription ID.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('metric', 'like', "%{$search}%")
                    ->orWhere('subscription_id', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $tenant) use ($search) {
                        $tenant->where('name', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Scope a query to filter by usage metric.
     *
     * @param list<string> $values
     */
    public function scopeFilterMetric(Builder $query, array $values): void
    {
        if ($values === []) {
            return;
        }

        $query->whereIn('metric', $values);
    }

    /**
     * Tenant this usage record belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Subscription this usage record is scoped to, if any.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metric' => UsageMetric::class,
            'quantity' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }
}
