<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Staff assignment at a store stored in the tenant database.
 *
 * @property int $id
 * @property string $store_id
 * @property string $employee_id
 * @property string|null $role_at_location
 * @property bool $is_primary_location
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LocationStaff extends TenantModel
{
    use HasFactory;

    protected $table = 'location_staff';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'employee_id',
        'role_at_location',
        'is_primary_location',
        'started_at',
        'ended_at',
    ];

    /**
     * Store this assignment belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Employee assigned to this store.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary_location' => 'boolean',
            'started_at' => 'date',
            'ended_at' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
