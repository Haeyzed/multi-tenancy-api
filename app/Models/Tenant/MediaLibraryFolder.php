<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Media library folder stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $parent_id
 * @property string|null $path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|MediaLibraryFolder search(?string $search)
 */
class MediaLibraryFolder extends TenantModel
{
    use HasFactory;

    protected $table = 'media_library_folders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'parent_id',
        'path',
    ];

    /**
     * Scope a query to search by name.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
