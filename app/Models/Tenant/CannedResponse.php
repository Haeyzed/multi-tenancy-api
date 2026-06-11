<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Pre-written support response template stored in the tenant database.
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $shortcut
 * @property string|null $body
 * @property string|null $category
 * @property bool $is_active
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CannedResponse search(?string $search)
 * @method static Builder|CannedResponse filterIsActive(array $statuses)
 */
class CannedResponse extends TenantModel
{
    use HasFactory;

    protected $table = 'canned_responses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'shortcut',
        'body',
        'category',
        'is_active',
        'created_by',
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
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
