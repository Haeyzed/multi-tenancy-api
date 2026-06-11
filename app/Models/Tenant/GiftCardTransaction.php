<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Gift card transactions stored in the tenant database.
 * @property int $id
 * @property int $gift_card_id
 * @property string|null $order_id
 * @property string $amount
 * @property string $type
 * @property string $balance_after
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GiftCardTransaction extends TenantModel
{
    use HasFactory;

    protected $table = 'gift_card_transactions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'gift_card_id',
        'order_id',
        'amount',
        'type',
        'balance_after',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related GiftCard.
     */
    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    /**
     * Related Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
