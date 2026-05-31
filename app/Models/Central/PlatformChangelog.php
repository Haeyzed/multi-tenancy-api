<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\ChangelogType;
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
