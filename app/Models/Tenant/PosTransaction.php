<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Pos transactions stored in the tenant database.
 * @property string $id
 * @property string $session_id
 * @property string $type
 * @property string|null $order_id
 * @property string $amount
 * @property string $payment_method
 * @property string|null $reference
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PosTransaction extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'pos_transactions';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'session_id',
        'type',
        'order_id',
        'amount',
        'payment_method',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
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
