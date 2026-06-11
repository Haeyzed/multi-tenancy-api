<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Models\Concerns\FilterableByTenant;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

/**
 * Domain hostname mapped to a tenant.
 *
 * @property int $id
 * @property string $tenant_id
 * @property string $domain
 * @property bool $is_primary
 * @property bool $is_fallback
 * @property bool $verified
 *
 * @method static Builder|Domain forTenant(?string $tenantId = null)
 * @method static Builder|Domain search(?string $search)
 */
class Domain extends BaseDomain
{
    use FilterableByTenant, HasFactory;

    /**
     * Scope a query to search by domain hostname.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Filter by verified/unverified tokens.
     *
     * @param list<string> $values
     */
    public function scopeFilterVerified(Builder $query, array $values): void
    {
        $mapped = QueryFilter::booleanVerified($values);

        $query->when($mapped !== [], fn(Builder $q) => $q->whereIn('verified', $mapped));
    }

    /**
     * Tenant that owns this domain.
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
            'is_primary' => 'boolean',
            'is_fallback' => 'boolean',
            'verified' => 'boolean',
        ];
    }
}
