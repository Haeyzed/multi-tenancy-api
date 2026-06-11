<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Payment methods stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property string $provider
 * @property bool $is_active
 * @property bool $is_test_mode
 * @property array<string, mixed>|null $config
 * @property int $sort_order
 * @property string|null $credentials_encrypted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PaymentMethod search(?string $search)
 * @method static Builder|PaymentMethod filterIsActive(array $statuses)
 */
class PaymentMethod extends TenantModel
{
    use HasFactory;

    protected $table = 'payment_methods';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'provider',
        'is_active',
        'is_test_mode',
        'config',
        'sort_order',
        'credentials_encrypted',
    ];

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = QueryFilter::booleanStatuses($statuses);

        $query->when($values !== [], fn(Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */


    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
            'config' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
