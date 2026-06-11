<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee dependents stored in the tenant database.
 * @property int $id
 * @property string $employee_id
 * @property string|null $name
 * @property string $relationship
 * @property Carbon|null $date_of_birth
 * @property string|null $phone
 * @property bool $is_beneficiary
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmployeeDependent search(?string $search)
 */
class EmployeeDependent extends TenantModel
{
    use HasFactory;

    protected $table = 'employee_dependents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'date_of_birth',
        'phone',
        'is_beneficiary',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_beneficiary' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
