<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Shipping zones stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property array<string, mixed>|null $countries
 * @property array<string, mixed>|null $regions
 * @property array<string, mixed>|null $postal_codes
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|ShippingZone search(?string $search)
 * @method static Builder|ShippingZone filterIsActive(array $statuses)
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

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<ShippingZone> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<ShippingZone> $query
     * @param list<string> $statuses
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

        $query->when($values !== [], fn (Builder $q): Builder => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
}
