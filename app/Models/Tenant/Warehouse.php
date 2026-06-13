<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Inventory fulfillment warehouse; separate from customer-facing stores.
 *
 * @property int $id
 * @property int|null $store_id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property array<string, mixed>|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string $timezone
 * @property int|null $manager_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Warehouse search(?string $search)
 * @method static Builder|Warehouse filterIsActive(array $statuses)
 */
class Warehouse extends TenantModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'warehouses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'name',
        'code',
        'type',
        'address',
        'phone',
        'email',
        'timezone',
        'manager_id',
        'is_active',
    ];

    /**
     * Optional store this warehouse primarily serves.
     *
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Zones inside this warehouse.
     *
     * @return HasMany<WarehouseZone, $this>
     */
    public function zones(): HasMany
    {
        return $this->hasMany(WarehouseZone::class);
    }

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<Warehouse> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param  list<string>  $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = [];

        foreach ($statuses as $status) {
            $values[] = match ($status) {
                'active' => true,
                'inactive' => false,
                default => null,
            };
        }

        $values = array_values(array_unique(array_filter(
            $values,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($values !== [], fn (Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'address' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
