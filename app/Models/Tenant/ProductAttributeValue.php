<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product attribute values stored in the tenant database.
 *
 * @property int $id
 * @property string $product_id
 * @property int $attribute_id
 * @property string|null $value
 * @property string|null $display_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ProductAttributeValue extends TenantModel
{
    use HasFactory;

    protected $table = 'product_attribute_values';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
        'display_value',
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
