<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Warehouse zones stored in the tenant database.
 * @property int $id
 * @property string $warehouse_id
 * @property string|null $name
 * @property string|null $code
 * @property string $type
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|WarehouseZone search(?string $search)
 */
class WarehouseZone extends TenantModel
{
    use HasFactory;

    protected $table = 'warehouse_zones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_id',
        'name',
        'code',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
