<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Pos sessions stored in the tenant database.
 *
 * @property int $id
 * @property int $register_id
 * @property int $cashier_id
 * @property string $opening_amount
 * @property string|null $expected_closing
 * @property string|null $actual_closing
 * @property string $difference
 * @property string|null $difference_reason
 * @property Carbon|null $opened_at
 * @property Carbon|null $closed_at
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PosSession extends TenantModel
{
    use HasFactory;

    protected $table = 'pos_sessions';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'register_id',
        'cashier_id',
        'opening_amount',
        'expected_closing',
        'actual_closing',
        'difference',
        'difference_reason',
        'opened_at',
        'closed_at',
        'status',
    ];

    /**
     * Filter by status values.
     *
     * @param list<string> $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn(Builder $q) => $q->whereIn('status', $statuses));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'expected_closing' => 'decimal:2',
            'actual_closing' => 'decimal:2',
            'difference' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
