<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Store addresses stored in the tenant database.
 *
 * @property int $id
 * @property string $type
 * @property string|null $name
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property string|null $phone
 * @property string|null $email
 * @property bool $is_default
 * @property string|null $lat
 * @property string|null $lng
 * @property array<string, mixed>|null $operating_hours
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|StoreAddress search(?string $search)
 */
class StoreAddress extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'store_addresses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'is_default',
        'lat',
        'lng',
        'operating_hours',
    ];

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'lat' => 'decimal:2',
            'lng' => 'decimal:2',
            'operating_hours' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
