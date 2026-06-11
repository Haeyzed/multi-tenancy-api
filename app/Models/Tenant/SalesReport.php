<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Sales reports stored in the tenant database.
 *
 * @property int $id
 * @property Carbon|null $date
 * @property int $orders_count
 * @property int $items_count
 * @property string $gross_sales
 * @property string $discounts
 * @property string $refunds
 * @property string $net_sales
 * @property string $shipping
 * @property string $tax
 * @property string $avg_order_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SalesReport extends TenantModel
{
    use HasFactory;

    protected $table = 'sales_reports';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'orders_count',
        'items_count',
        'gross_sales',
        'discounts',
        'refunds',
        'net_sales',
        'shipping',
        'tax',
        'avg_order_value',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'gross_sales' => 'decimal:2',
            'discounts' => 'decimal:2',
            'refunds' => 'decimal:2',
            'net_sales' => 'decimal:2',
            'shipping' => 'decimal:2',
            'tax' => 'decimal:2',
            'avg_order_value' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
