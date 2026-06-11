<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Customer segments stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property array<string, mixed>|null $conditions
 * @property int $user_count
 * @property bool $is_dynamic
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_dynamic' => 'boolean',
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
