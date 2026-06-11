<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product options stored in the tenant database.
 * @property int $id
 * @property int $option_group_id
 * @property string|null $name
 * @property string|null $value
 * @property string|null $color_hex
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ProductOption search(?string $search)
 */
class ProductOption extends TenantModel
{
    use HasFactory;

    protected $table = 'product_options';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'option_group_id',
        'name',
        'value',
        'color_hex',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
            );
        });
    }
}
