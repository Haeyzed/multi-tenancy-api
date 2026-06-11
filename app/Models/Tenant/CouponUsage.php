<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Coupon usage stored in the tenant database.
 * @property int $id
 * @property int $coupon_id
 * @property string $order_id
 * @property string|null $user_id
 * @property string $discount_amount
 * @property Carbon|null $used_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CouponUsage extends TenantModel
{
    use HasFactory;

    protected $table = 'coupon_usage';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'coupon_id',
        'order_id',
        'user_id',
        'discount_amount',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Coupon.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Related Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Related User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
