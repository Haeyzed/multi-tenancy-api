<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Knowledge base article stored in the tenant database.
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $category
 * @property string|null $content
 * @property bool $is_published
 * @property int $view_count
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|KnowledgeBaseArticle search(?string $search)
 */
class KnowledgeBaseArticle extends TenantModel
{
    use HasFactory;

    protected $table = 'knowledge_base';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'category',
        'content',
        'is_published',
        'view_count',
        'created_by',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<KnowledgeBaseArticle> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
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
            'is_published' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
