<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Goods receipt line item stored in the tenant database.
 *
 * @property int $id
 * @property string $grn_id
 * @property int $po_item_id
 * @property int $received_qty
 * @property int $accepted_qty
 * @property int $rejected_qty
 * @property string|null $rejection_reason
 * @property string|null $batch_number
 * @property Carbon|null $expiry_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GoodsReceiptItem extends TenantModel
{
    use HasFactory;

    protected $table = 'goods_receipt_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'grn_id',
        'po_item_id',
        'received_qty',
        'accepted_qty',
        'rejected_qty',
        'rejection_reason',
        'batch_number',
        'expiry_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
