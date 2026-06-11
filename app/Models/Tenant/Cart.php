<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Shopping cart stored in the tenant database.
 *
 * @property string $id
 * @property string|null $user_id
 * @property string|null $session_id
 * @property string|null $email
 * @property string|null $currency
 * @property string $subtotal
 * @property string $discount_total
 * @property string $tax_total
 * @property string $shipping_total
 * @property string $grand_total
 * @property string|null $coupon_code
 * @property int|null $shipping_address_id
 * @property int|null $billing_address_id
 * @property int|null $shipping_method_id
 * @property string|null $notes
 * @property bool $abandoned_cart_email_sent
 * @property Carbon|null $last_activity_at
 * @property Carbon|null $expires_at
 * @property string|null $converted_to_order_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Cart search(?string $search)
 */
class Cart extends TenantModel
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $table = 'carts';
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'email',
        'currency',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'grand_total',
        'coupon_code',
        'shipping_address_id',
        'billing_address_id',
        'shipping_method_id',
        'notes',
        'abandoned_cart_email_sent',
        'last_activity_at',
        'expires_at',
        'converted_to_order_id',
    ];

    /**
     * Registered user who owns this cart, if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to search by email.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('email', 'like', "%{$search}%");
            });
        });
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
            'abandoned_cart_email_sent' => 'boolean',
            'last_activity_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
