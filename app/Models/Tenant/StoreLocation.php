<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Store locations stored in the tenant database.
 * @property string $id
 * @property string|null $name
 * @property string|null $code
 * @property string $type
 * @property array<string, mixed>|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $manager_id
 * @property string|null $timezone
 * @property string|null $currency
 * @property string $tax_rate
 * @property array<string, mixed>|null $opening_hours
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|StoreLocation search(?string $search)
 */
class StoreLocation extends TenantModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'store_locations';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'phone',
        'email',
        'manager_id',
        'timezone',
        'currency',
        'tax_rate',
        'opening_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'tax_rate' => 'decimal:2',
            'opening_hours' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
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
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            );
        });
    }
}
