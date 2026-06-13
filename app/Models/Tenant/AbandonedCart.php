<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Abandoned cart recovery record stored in the tenant database.
 *
 * @property int $id
 * @property int $cart_id
 * @property Carbon|null $email_sent_at
 * @property bool $email_opened
 * @property bool $email_clicked
 * @property Carbon|null $recovered_at
 * @property string|null $recovery_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AbandonedCart extends TenantModel
{
    use HasFactory;

    protected $table = 'abandoned_carts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'email_sent_at',
        'email_opened',
        'email_clicked',
        'recovered_at',
        'recovery_amount',
    ];

    /**
     * Cart that was abandoned.
     *
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_sent_at' => 'datetime',
            'email_opened' => 'boolean',
            'email_clicked' => 'boolean',
            'recovered_at' => 'datetime',
            'recovery_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
