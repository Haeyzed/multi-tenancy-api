<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Coupons stored in the tenant database.
 * @property int $id
 * @property string|null $code
 * @property string $type
 * @property string $value
 * @property string|null $min_purchase
 * @property string|null $max_discount
 * @property int|null $usage_limit
 * @property int $usage_count
 * @property int $per_customer_limit
 * @property string $applies_to
 * @property array<string, mixed>|null $target_ids
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property bool $is_active
 * @property string|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Coupon search(?string $search)
 */
class Coupon extends TenantModel
{
    use HasFactory;

    protected $table = 'coupons';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'usage_count',
        'per_customer_limit',
        'applies_to',
        'target_ids',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'target_ids' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('code', 'like', "%{$search}%")
            );
        });
    }
}
