<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Pages stored in the tenant database.
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $slug
 * @property array<string, mixed>|null $content
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $template
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property int $sort_order
 * @property string|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Page search(?string $search)
 */
class Page extends TenantModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pages';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'template',
        'is_published',
        'published_at',
        'sort_order',
        'created_by',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<Page> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
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
            'content' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
