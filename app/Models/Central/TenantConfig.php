<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant-specific configuration key-value entry.
 *
 * @property int $id
 * @property string $tenant_id
 * @property string $key
 * @property string|null $value
 * @property bool $encrypted
 *
 * @method static Builder|TenantConfig forTenant(?string $tenantId = null)
 * @method static Builder|TenantConfig search(?string $search)
 */
class TenantConfig extends Model
{
    use FilterableByTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'encrypted',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'encrypted' => 'boolean',
        ];
    }

    /**
     * Scope a query to search by key or value.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Tenant this configuration belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
