<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Promotional banner stored in the tenant database.
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $cta_text
 * @property string|null $cta_url
 * @property int|null $media_id
 * @property string $position
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Banner search(?string $search)
 * @method static Builder|Banner filterIsActive(array $statuses)
 */
class Banner extends TenantModel
{
    use HasFactory;

    protected $table = 'banners';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'subtitle',
        'cta_text',
        'cta_url',
        'media_id',
        'position',
        'start_date',
        'end_date',
        'is_active',
        'sort_order',
    ];

    /**
     * Scope a query to search by title.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
