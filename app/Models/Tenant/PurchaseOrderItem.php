<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Purchase order items stored in the tenant database.
 *
 * @property int $id
 * @property string $po_id
 * @property int|null $supplier_product_id
 * @property string|null $product_id
 * @property int $quantity
 * @property string $unit_cost
 * @property int $received_qty
 * @property string $line_total
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PurchaseOrderItem extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_order_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'po_id',
        'supplier_product_id',
        'product_id',
        'quantity',
        'unit_cost',
        'received_qty',
        'line_total',
        'notes',
    ];

    /**
     * Related SupplierProduct.
     */
    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }

    /**
     * Related Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
