<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Product brand stored in the tenant database.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $logo_media_id
 * @property string|null $website_url
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Brand search(?string $search)
 * @method static Builder|Brand filterIsActive(array $statuses)
 * @method static Builder|Brand filterTrashed(array $tokens)
 */
class Brand extends TenantModel
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $table = 'brands';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'logo_media_id',
        'website_url',
        'is_active',
        'sort_order',
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
     * Logo media file for this brand.
     *
     * @return BelongsTo<Media, $this>
     */
    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    /**
     * Products assigned to this brand.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope a query to search by name, slug, or description.
     *
     * @param Builder<Brand> $query
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
     * @param Builder<Brand> $query
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
            static fn(?bool $value): bool => $value !== null,
        )));

        $query->when($values !== [], fn (Builder $q): Builder => $q->whereIn('is_active', $values));
    }

    /**
     * Filter by soft-delete visibility tokens (only, with).
     *
     * @param Builder<Brand> $query
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
