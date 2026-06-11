<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Goods receipt notes stored in the tenant database.
 * @property string $id
 * @property string $po_id
 * @property string|null $grn_number
 * @property Carbon|null $received_date
 * @property string $received_by
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GoodsReceiptNote extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'goods_receipt_notes';

    public $incrementing = false;

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

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
