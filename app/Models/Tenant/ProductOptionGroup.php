<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Product option groups stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property int $sort_order
 * @property bool $is_color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ProductOptionGroup search(?string $search)
 */
class ProductOptionGroup extends TenantModel
{
    use HasFactory;

    protected $table = 'product_option_groups';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sort_order',
        'is_color',
    ];

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%");
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
            'is_color' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
