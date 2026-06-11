<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Blog posts stored in the tenant database.
 * @property int $id
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $excerpt
 * @property string|null $body
 * @property int|null $featured_media_id
 * @property string|null $author_id
 * @property int|null $category_id
 * @property array<string, mixed>|null $tags
 * @property array<string, mixed>|null $meta
 * @property string $status
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|BlogPost search(?string $search)
 */
class BlogPost extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'blog_posts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_media_id',
        'author_id',
        'category_id',
        'tags',
        'meta',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'meta' => 'array',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Related Category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
            );
        });
    }
}
