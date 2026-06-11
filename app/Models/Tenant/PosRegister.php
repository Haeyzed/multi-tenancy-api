<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Pos registers stored in the tenant database.
 * @property string $id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $warehouse_id
 * @property int|null $location_id
 * @property string $status
 * @property string|null $current_cashier_id
 * @property string $opening_amount
 * @property string|null $closing_amount
 * @property Carbon|null $opened_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PosRegister search(?string $search)
 */
class PosRegister extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'pos_registers';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'warehouse_id',
        'location_id',
        'status',
        'current_cashier_id',
        'opening_amount',
        'closing_amount',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'closing_amount' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
            );
        });
    }
}
