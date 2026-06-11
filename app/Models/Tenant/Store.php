<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Storefront and/or physical retail location operated by the tenant.
 *
 * A tenant may run multiple stores (online, retail, popup, etc.).
 * Use {@see StoreAddress} for additional addresses (billing, pickup, return).
 * Use {@see Warehouse} for inventory fulfillment centers.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $code
 * @property string $type
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $whatsapp
 * @property string|null $website_url
 * @property array<string, mixed>|null $address
 * @property string $timezone
 * @property string $currency
 * @property string $tax_rate
 * @property array<string, mixed>|null $opening_hours
 * @property string|null $manager_id
 * @property int|null $logo_media_id
 * @property int|null $favicon_media_id
 * @property bool $is_primary
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Store search(?string $search)
 * @method static Builder|Store filterIsActive(array $statuses)
 * @method static Builder|Store filterType(array $types)
 */
class Store extends TenantModel
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'stores';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'code',
        'type',
        'tagline',
        'description',
        'email',
        'phone',
        'whatsapp',
        'website_url',
        'address',
        'timezone',
        'currency',
        'tax_rate',
        'opening_hours',
        'manager_id',
        'logo_media_id',
        'favicon_media_id',
        'is_primary',
        'is_active',
        'sort_order',
    ];

    /**
     * Logo media file for this store.
     */
    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    /**
     * Favicon media file for this store.
     */
    public function faviconMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'favicon_media_id');
    }

    /**
     * Operational settings scoped to this store.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(StoreSetting::class);
    }

    /**
     * All addresses linked to this store.
     *
     * @return HasMany<StoreAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(StoreAddress::class);
    }

    /**
     * Default address used for display and checkout defaults.
     */
    public function primaryAddress(): HasOne
    {
        return $this->hasOne(StoreAddress::class)->where('is_default', true);
    }

    /**
     * Warehouses that fulfill inventory for this store.
     *
     * @return HasMany<Warehouse, $this>
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Scope a query to search by name, slug, or code.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
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
        $values = QueryFilter::booleanStatuses($statuses);

        $query->when($values !== [], fn (Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Filter by store type tokens.
     *
     * @param  list<string>  $types
     */
    public function scopeFilterType(Builder $query, array $types): void
    {
        $query->when($types !== [], fn (Builder $q) => $q->whereIn('type', $types));
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
            'opening_hours' => 'array',
            'tax_rate' => 'decimal:2',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
