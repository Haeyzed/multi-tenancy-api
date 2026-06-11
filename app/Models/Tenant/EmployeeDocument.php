<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee documents stored in the tenant database.
 * @property int $id
 * @property string $employee_id
 * @property string $document_type
 * @property string|null $title
 * @property int|null $media_id
 * @property Carbon|null $expiry_date
 * @property bool $is_verified
 * @property string|null $verified_by
 * @property Carbon|null $verified_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmployeeDocument search(?string $search)
 */
class EmployeeDocument extends TenantModel
{
    use HasFactory;

    protected $table = 'employee_documents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'document_type',
        'title',
        'media_id',
        'expiry_date',
        'is_verified',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
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
                $inner->where('title', 'like', "%{$search}%")
            );
        });
    }
}
