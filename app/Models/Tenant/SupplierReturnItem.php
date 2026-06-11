<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Supplier return items stored in the tenant database.
 *
 * @property int $id
 * @property string $return_id
 * @property int $po_item_id
 * @property int $returned_qty
 * @property string $refund_amount
 * @property string $condition
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SupplierReturnItem extends TenantModel
{
    use HasFactory;

    protected $table = 'supplier_return_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'return_id',
        'po_item_id',
        'returned_qty',
        'refund_amount',
        'condition',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
