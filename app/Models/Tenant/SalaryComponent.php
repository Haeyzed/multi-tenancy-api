<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Salary components stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property string $type
 * @property string $category
 * @property bool $is_taxable
 * @property bool $is_percentage
 * @property string|null $default_value
 * @property string|null $formula
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SalaryComponent search(?string $search)
 */
class SalaryComponent extends TenantModel
{
    use HasFactory;

    protected $table = 'salary_components';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'category',
        'is_taxable',
        'is_percentage',
        'default_value',
        'formula',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'is_percentage' => 'boolean',
            'default_value' => 'decimal:2',
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
