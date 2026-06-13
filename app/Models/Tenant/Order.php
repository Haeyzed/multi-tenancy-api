<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Orders stored in the tenant database.
 *
 * @property int $id
 * @property string|null $order_number
 * @property int|null $user_id
 * @property string|null $guest_email
 * @property string $status
 * @property string $payment_status
 * @property string $fulfillment_status
 * @property string|null $currency
 * @property string $subtotal
 * @property string $discount_total
 * @property string $tax_total
 * @property string $shipping_total
 * @property string $grand_total
 * @property string $total_paid
 * @property string $total_refunded
 * @property string|null $coupon_code
 * @property string $coupon_discount
 * @property array<string, mixed>|null $shipping_address
 * @property array<string, mixed>|null $billing_address
 * @property array<string, mixed>|null $shipping_method
 * @property string|null $customer_notes
 * @property string|null $staff_notes
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $source
 * @property Carbon|null $placed_at
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Order search(?string $search)
 * @method static Builder|Order filterStatus(array $statuses)
 */
class Order extends TenantModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'orders';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'guest_email',
        'status',
        'payment_status',
        'fulfillment_status',
        'currency',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'grand_total',
        'total_paid',
        'total_refunded',
        'coupon_code',
        'coupon_discount',
        'shipping_address',
        'billing_address',
        'shipping_method',
        'customer_notes',
        'staff_notes',
        'ip_address',
        'user_agent',
        'source',
        'placed_at',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    /**
     * Related User.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<Order> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('order_number', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by status values.
     *
     * @param list<string> $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn(Builder $q) => $q->whereIn('status', $statuses));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'total_refunded' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'shipping_method' => 'array',
            'placed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
