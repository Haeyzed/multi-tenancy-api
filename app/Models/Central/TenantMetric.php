<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Daily aggregated usage and revenue metrics for a tenant.
 *
 * @property int $id
 * @property string $tenant_id
 * @property Carbon $metric_date
 * @property int $total_orders
 * @property string $total_revenue
 * @property int $total_products
 * @property int $total_customers
 * @property int $storage_used_mb
 * @property int $bandwidth_used_mb
 * @property int $api_calls
 *
 * @method static Builder|TenantMetric forTenant(?string $tenantId = null)
 * @method static Builder|TenantMetric search(?string $search)
 * @method static Builder|TenantMetric filterDateRange(?string $startDate, ?string $endDate)
 */
class TenantMetric extends Model
{
    use FilterableByTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'metric_date',
        'total_orders',
        'total_revenue',
        'total_products',
        'total_customers',
        'storage_used_mb',
        'bandwidth_used_mb',
        'api_calls',
    ];

    /**
     * Scope a query to search by metric date.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('metric_date', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $tenant) use ($search) {
                        $tenant->where('name', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Scope a query to filter by metric date range.
     */
    public function scopeFilterDateRange(
        Builder $query,
        ?string $startDate,
        ?string $endDate,
    ): void
    {
        if ($startDate !== null && $startDate !== '') {
            $query->whereDate('metric_date', '>=', Carbon::parse($startDate));
        }

        if ($endDate !== null && $endDate !== '') {
            $query->whereDate('metric_date', '<=', Carbon::parse($endDate));
        }
    }

    /**
     * Tenant these metrics belong to.
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
            'metric_date' => 'date',
            'total_revenue' => 'decimal:2',
        ];
    }
}
