<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product views stored in the tenant database.
 *
 * @property int $id
 * @property string $product_id
 * @property Carbon|null $date
 * @property int $views
 * @property int $unique_views
 * @property int $add_to_carts
 * @property int $purchases
 * @property string $revenue
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ProductView extends TenantModel
{
    use HasFactory;

    protected $table = 'product_views';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'date',
        'views',
        'unique_views',
        'add_to_carts',
        'purchases',
        'revenue',
    ];

    /**
     * Related Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'revenue' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
