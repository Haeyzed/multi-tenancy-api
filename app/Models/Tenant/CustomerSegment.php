<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Customer segment stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property array<string, mixed>|null $conditions
 * @property int $user_count
 * @property bool $is_dynamic
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|CustomerSegment search(?string $search)
 */
class CustomerSegment extends TenantModel
{
    use HasFactory;

    protected $table = 'customer_segments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'conditions',
        'user_count',
        'is_dynamic',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<CustomerSegment> $query
     * @param string|null $search
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
            'conditions' => 'array',
            'is_dynamic' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
