<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Work locations stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property string $type
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $postal_code
 * @property string|null $phone
 * @property string|null $lat
 * @property string|null $lng
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|WorkLocation search(?string $search)
 */
class WorkLocation extends TenantModel
{
    use HasFactory;

    protected $table = 'work_locations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'lat',
        'lng',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:2',
            'lng' => 'decimal:2',
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
