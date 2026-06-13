<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Product attributes stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string $type
 * @property bool $is_filterable
 * @property bool $is_visible
 * @property int $sort_order
 * @property array<string, mixed>|null $options
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|ProductAttribute search(?string $search)
 */
class ProductAttribute extends TenantModel
{
    use HasFactory;

    protected $table = 'product_attributes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_filterable',
        'is_visible',
        'sort_order',
        'options',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<ProductAttribute> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
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
            'is_filterable' => 'boolean',
            'is_visible' => 'boolean',
            'options' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
