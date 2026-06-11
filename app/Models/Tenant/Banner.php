<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Banners stored in the tenant database.
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

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
            );
        });
    }
}
