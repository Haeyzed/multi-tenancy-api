<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Departments stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $code
 * @property string|null $description
 * @property int|null $parent_id
 * @property string|null $manager_id
 * @property int|null $location_id
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Department search(?string $search)
 */
class Department extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'departments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'parent_id',
        'manager_id',
        'location_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
            );
        });
    }
}
