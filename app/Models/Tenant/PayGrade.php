<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Pay grades stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property string $min_salary
 * @property string $max_salary
 * @property string|null $currency
 * @property string|null $description
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PayGrade search(?string $search)
 */
class PayGrade extends TenantModel
{
    use HasFactory;

    protected $table = 'pay_grades';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'min_salary',
        'max_salary',
        'currency',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_salary' => 'decimal:2',
            'max_salary' => 'decimal:2',
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
                    ->orWhere('code', 'like', "%{$search}%")
            );
        });
    }
}
