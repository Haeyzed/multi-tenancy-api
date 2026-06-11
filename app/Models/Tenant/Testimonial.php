<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Testimonials stored in the tenant database.
 *
 * @property int $id
 * @property string|null $author_name
 * @property string|null $author_title
 * @property string|null $content
 * @property int|null $rating
 * @property int|null $media_id
 * @property bool $is_featured
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Testimonial extends TenantModel
{
    use HasFactory;

    protected $table = 'testimonials';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'author_name',
        'author_title',
        'content',
        'rating',
        'media_id',
        'is_featured',
        'sort_order',
        'is_active',
    ];

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
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
