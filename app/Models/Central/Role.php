<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as SpatieRole;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * Platform administrator role stored in the central database.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Role search(?string $search)
 */
class Role extends SpatieRole
{
    use CentralConnection;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * Scope a query to search by name or guard.
     *
     * @param Builder<Role> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search): void {
            $q->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('guard_name', 'like', "%{$search}%");
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
