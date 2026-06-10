<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One-time token used for super-admin tenant impersonation.
 *
 * @property int $id
 * @property string $tenant_id
 * @property int $admin_id
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 *
 * @method static Builder|TenantImpersonationToken forTenant(?string $tenantId = null)
 * @method static Builder|TenantImpersonationToken search(?string $search)
 * @method static Builder|TenantImpersonationToken filterStatus(array $values)
 */
class TenantImpersonationToken extends Model
{
    use FilterableByTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'admin_id',
        'token',
        'expires_at',
        'used_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to search by token.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('token', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $tenant) use ($search) {
                        $tenant->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('administrator', function (Builder $admin) use ($search) {
                        $admin->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Scope a query to filter by token lifecycle status.
     *
     * @param  list<string>  $values
     */
    public function scopeFilterStatus(Builder $query, array $values): void
    {
        if ($values === []) {
            return;
        }

        $query->where(function (Builder $q) use ($values) {
            foreach ($values as $status) {
                match ($status) {
                    'valid' => $q->orWhere(function (Builder $sub) {
                        $sub->whereNull('used_at')
                            ->where('expires_at', '>', now());
                    }),
                    'used' => $q->orWhereNotNull('used_at'),
                    'expired' => $q->orWhere(function (Builder $sub) {
                        $sub->whereNull('used_at')
                            ->where('expires_at', '<=', now());
                    }),
                    default => null,
                };
            }
        });
    }

    /**
     * Tenant being impersonated.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Platform administrator who issued this token.
     */
    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
