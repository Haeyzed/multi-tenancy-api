<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Models\Concerns\FilterableByTenant;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * API key granting programmatic access for a tenant.
 *
 * @property int $id
 * @property string $tenant_id
 * @property string $name
 * @property string $key_hash
 * @property array<string, mixed>|null $permissions
 * @property Carbon|null $last_used_at
 * @property Carbon|null $expires_at
 * @property bool $is_active
 *
 * @method static Builder|ApiKey forTenant(?string $tenantId = null)
 * @method static Builder|ApiKey search(?string $search)
 */
class ApiKey extends Model
{
    use FilterableByTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'key_hash',
        'permissions',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    /**
     * Scope a query to search by name.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Filter by active/inactive tokens.
     *
     * @param list<string> $values
     */
    public function scopeFilterIsActive(Builder $query, array $values): void
    {
        $mapped = QueryFilter::booleanStatuses($values);

        $query->when($mapped !== [], fn(Builder $q) => $q->whereIn('is_active', $mapped));
    }

    /**
     * Tenant that owns this API key.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
