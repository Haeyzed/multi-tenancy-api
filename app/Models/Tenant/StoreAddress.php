<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Physical address belonging to a store (billing, shipping, pickup, return, etc.).
 *
 * @property int $id
 * @property string $store_id
 * @property string $type
 * @property string $name
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $city
 * @property string $state
 * @property string $postal_code
 * @property string $country
 * @property string|null $phone
 * @property string|null $email
 * @property bool $is_default
 * @property string|null $lat
 * @property string|null $lng
 * @property array<string, mixed>|null $operating_hours
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
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
        'store_id',
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
     * Store this address belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope a query to search by name, city, or email.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
            'operating_hours' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
