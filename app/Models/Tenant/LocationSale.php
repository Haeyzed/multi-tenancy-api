<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Daily sales summary for a store stored in the tenant database.
 *
 * @property int $id
 * @property string $store_id
 * @property Carbon|null $date
 * @property string $total_sales
 * @property int $order_count
 * @property int $item_count
 * @property string $cash_sales
 * @property string $card_sales
 * @property string $other_sales
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LocationSale extends TenantModel
{
    use HasFactory;

    protected $table = 'location_sales';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'date',
        'total_sales',
        'order_count',
        'item_count',
        'cash_sales',
        'card_sales',
        'other_sales',
    ];

    /**
     * Store this sales summary belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_sales' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'card_sales' => 'decimal:2',
            'other_sales' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
