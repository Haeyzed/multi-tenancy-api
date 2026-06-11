<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Shipping zones stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property array<string, mixed>|null $countries
 * @property array<string, mixed>|null $regions
 * @property array<string, mixed>|null $postal_codes
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ShippingZone search(?string $search)
 */
class ShippingZone extends TenantModel
{
    use HasFactory;

    protected $table = 'shipping_zones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'countries',
        'regions',
        'postal_codes',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'regions' => 'array',
            'postal_codes' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
            );
        });
    }
}
