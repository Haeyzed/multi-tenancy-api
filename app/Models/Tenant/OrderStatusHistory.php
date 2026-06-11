<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Order status history stored in the tenant database.
 *
 * @property int $id
 * @property string $order_id
 * @property string|null $status
 * @property string|null $previous_status
 * @property string|null $changed_by_type
 * @property string|null $changed_by_id
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrderStatusHistory extends TenantModel
{
    use HasFactory;

    protected $table = 'order_status_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'status',
        'previous_status',
        'changed_by_type',
        'changed_by_id',
        'reason',
    ];

    /**
     * Related Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
