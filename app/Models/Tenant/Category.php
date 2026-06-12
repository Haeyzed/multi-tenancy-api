<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Product category stored in the tenant database.
 *
 * @property string $id
 * @property string|null $parent_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property int|null $banner_media_id
 * @property int|null $icon_media_id
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_featured
 * @property bool $show_in_menu
 * @property int $depth
 * @property string|null $path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Category search(?string $search)
 * @method static Builder|Category filterIsActive(array $statuses)
 * @method static Builder|Category filterIsFeatured(array $values)
 * @method static Builder|Category filterShowInMenu(array $values)
 */
class Category extends TenantModel
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $table = 'categories';
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'banner_media_id',
        'icon_media_id',
        'sort_order',
        'is_active',
        'is_featured',
        'show_in_menu',
        'depth',
        'path',
    ];

    /**
     * Parent category in the tree.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Direct child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Banner image for this category.
     */
    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    /**
     * Icon image for this category.
     */
    public function iconMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'icon_media_id');
    }

    /**
     * Products with this category as primary assignment.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Pivot rows linking additional products to this category.
     */
    public function categoryProducts(): HasMany
    {
        return $this->hasMany(CategoryProduct::class);
    }

    /**
     * Scope a query to search by name, slug, or description.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = QueryFilter::booleanStatuses($statuses);

        $query->when($values !== [], fn(Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Filter by featured/unfeatured tokens.
     *
     * @param list<string> $values
     */
    public function scopeFilterIsFeatured(Builder $query, array $values): void
    {
        $mapped = QueryFilter::booleanFeatured($values);

        $query->when($mapped !== [], fn(Builder $q) => $q->whereIn('is_featured', $mapped));
    }

    /**
     * Filter by menu visibility tokens (in_menu, hidden).
     *
     * @param list<string> $values
     */
    public function scopeFilterShowInMenu(Builder $query, array $values): void
    {
        $mapped = QueryFilter::booleanShowInMenu($values);

        $query->when($mapped !== [], fn(Builder $q) => $q->whereIn('show_in_menu', $mapped));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'show_in_menu' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
