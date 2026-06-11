<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Leave types stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $color
 * @property bool $is_paid
 * @property int $default_days_per_year
 * @property bool $is_carry_forward
 * @property int $max_carry_days
 * @property bool $requires_approval
 * @property int $min_notice_days
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|LeaveType search(?string $search)
 */
class LeaveType extends TenantModel
{
    use HasFactory;

    protected $table = 'leave_types';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_paid',
        'default_days_per_year',
        'is_carry_forward',
        'max_carry_days',
        'requires_approval',
        'min_notice_days',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'is_carry_forward' => 'boolean',
            'requires_approval' => 'boolean',
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
                    ->orWhere('slug', 'like', "%{$search}%")
            );
        });
    }
}
