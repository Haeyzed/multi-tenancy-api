<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Loyalty transactions stored in the tenant database.
 * @property int $id
 * @property string $account_id
 * @property string $type
 * @property int $points
 * @property string|null $order_id
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LoyaltyTransaction extends TenantModel
{
    use HasFactory;

    protected $table = 'loyalty_transactions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'account_id',
        'type',
        'points',
        'order_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
