<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\ChangelogType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Published platform changelog entry.
 *
 * @property int $id
 * @property string $version
 * @property string $title
 * @property string $description
 * @property ChangelogType $type
 * @property bool $is_published
 * @property Carbon|null $published_at
 */
class PlatformChangelog extends Model
{
    use HasFactory;

    /**
     * Non-standard table name used by the central database schema.
     *
     * @var string
     */
    protected $table = 'platform_changelog';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'version',
        'title',
        'description',
        'type',
        'is_published',
        'published_at',
    ];

    /**
     * Scope a query to search by version, title, or description.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('version', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });
    }

    /**
     * @param list<string> $values
     */
    public function scopeFilterType(Builder $query, array $values): void
    {
        $query->when($values !== [], fn(Builder $q) => $q->whereIn('type', $values));
    }

    /**
     * @param list<string> $values
     */
    public function scopeFilterIsPublished(Builder $query, array $values): void
    {
        $mapped = [];

        foreach ($values as $value) {
            $mapped[] = match ($value) {
                'published' => true,
                'draft' => false,
                default => null,
            };
        }

        $mapped = array_values(array_unique(array_filter(
            $mapped,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($mapped !== [], fn(Builder $q) => $q->whereIn('is_published', $mapped));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ChangelogType::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
