<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Goods receipt note stored in the tenant database.
 *
 * @property string $id
 * @property string $po_id
 * @property string|null $grn_number
 * @property Carbon|null $received_date
 * @property string $received_by
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|GoodsReceiptNote filterStatus(array $statuses)
 */
class GoodsReceiptNote extends TenantModel
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $table = 'goods_receipt_notes';
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'po_id',
        'grn_number',
        'received_date',
        'received_by',
        'status',
        'notes',
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
            'received_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
