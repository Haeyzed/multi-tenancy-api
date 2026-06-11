<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Transfer items stored in the tenant database.
 *
 * @property int $id
 * @property string $transfer_id
 * @property string $product_id
 * @property string|null $variant_id
 * @property int $quantity
 * @property int $received_qty
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TransferItem extends TenantModel
{
    use HasFactory;

    protected $table = 'transfer_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'transfer_id',
        'product_id',
        'variant_id',
        'quantity',
        'received_qty',
        'notes',
    ];

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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
