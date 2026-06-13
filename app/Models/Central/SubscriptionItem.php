<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Billable line item attached to a subscription.
 *
 * @property int $id
 * @property int $subscription_id
 * @property int $plan_id
 * @property int $quantity
 * @property int $unit_price
 * @property int $total_price
 */
class SubscriptionItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscription_id',
        'plan_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * Subscription this line item belongs to.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Plan billed by this line item.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
