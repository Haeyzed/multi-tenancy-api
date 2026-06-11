<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Pos transactions stored in the tenant database.
 *
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

    public $incrementing = false;
    protected $table = 'pos_transactions';
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
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
