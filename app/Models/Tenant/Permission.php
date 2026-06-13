<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Tenant-scoped permission record.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Permission search(?string $search)
 */
class Permission extends SpatiePermission
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'module',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<Permission> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('guard_name', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%");
            });
        });
    }
}
