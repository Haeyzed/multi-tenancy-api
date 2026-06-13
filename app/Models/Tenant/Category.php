<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Product category stored in the tenant database.
 *
 * @property int $id
 * @property int|null $parent_id
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
 * @method static Builder|Category filterTrashed(array $tokens)
 */
class Category extends TenantModel
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $table = 'categories';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'name',
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
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Parent category in the tree.
     *
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Direct child categories.
     *
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Banner image for this category.
     *
     * @return BelongsTo<Media, $this>
     */
    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    /**
     * Icon image for this category.
     *
     * @return BelongsTo<Media, $this>
     */
    public function iconMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'icon_media_id');
    }

    /**
     * Products linked to this category via pivot table.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withPivot(['is_primary', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * Pivot rows linking products to this category.
     *
     * @return HasMany<CategoryProduct, $this>
     */
    public function categoryProducts(): HasMany
    {
        return $this->hasMany(CategoryProduct::class);
    }

    /**
     * Scope a query to search by name, slug, or description.
     *
     * @param Builder<Category> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search): void {
            $q->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<Category> $query
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
     * Filter by featured/unfeatured tokens.
     *
     * @param Builder<Category> $query
     * @param list<string> $values
     */
    public function scopeFilterIsFeatured(Builder $query, array $values): void
    {
        $mapped = [];

        foreach ($values as $value) {
            $mapped[] = match ($value) {
                'featured' => true,
                'unfeatured' => false,
                default => null,
            };
        }

        $mapped = array_values(array_unique(array_filter(
            $mapped,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($mapped !== [], fn (Builder $q): Builder => $q->whereIn('is_featured', $mapped));
    }

    /**
     * Filter by menu visibility tokens (in_menu, hidden).
     *
     * @param Builder<Category> $query
     * @param list<string> $values
     */
    public function scopeFilterShowInMenu(Builder $query, array $values): void
    {
        $mapped = [];

        foreach ($values as $value) {
            $mapped[] = match ($value) {
                'in_menu' => true,
                'hidden' => false,
                default => null,
            };
        }

        $mapped = array_values(array_unique(array_filter(
            $mapped,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($mapped !== [], fn (Builder $q): Builder => $q->whereIn('show_in_menu', $mapped));
    }

    /**
     * Filter by soft-delete visibility tokens (only, with).
     *
     * @param Builder<Category> $query
     * @param list<string> $tokens
     */
    public function scopeFilterTrashed(Builder $query, array $tokens): void
    {
        if ($tokens === []) {
            return;
        }

        if (in_array('only', $tokens, true)) {
            $query->onlyTrashed();

            return;
        }

        if (in_array('with', $tokens, true)) {
            $query->withTrashed();
        }
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
