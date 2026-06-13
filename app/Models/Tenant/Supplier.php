<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Suppliers stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $code
 * @property string|null $contact_person
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website_url
 * @property string|null $tax_number
 * @property string $payment_terms
 * @property string|null $currency
 * @property array<string, mixed>|null $address
 * @property string|null $bank_details
 * @property string $rating
 * @property bool $is_active
 * @property string|null $notes
 * @property string|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Supplier search(?string $search)
 * @method static Builder|Supplier filterIsActive(array $statuses)
 */
class Supplier extends TenantModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'suppliers';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'code',
        'contact_person',
        'email',
        'phone',
        'website_url',
        'tax_number',
        'payment_terms',
        'currency',
        'address',
        'bank_details',
        'rating',
        'is_active',
        'notes',
        'created_by',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<Supplier> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<Supplier> $query
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = [];

        foreach ($statuses as $status) {
            $values[] = match ($status) {
                'active' => true,
                'inactive' => false,
                default => null,
            };
        }

        $values = array_values(array_unique(array_filter(
            $values,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($values !== [], fn (Builder $q): Builder => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'address' => 'array',
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
