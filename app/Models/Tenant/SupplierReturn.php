<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Supplier returns stored in the tenant database.
 * @property string $id
 * @property string $po_id
 * @property string $grn_id
 * @property string|null $return_number
 * @property string $status
 * @property Carbon|null $return_date
 * @property string|null $reason
 * @property string $total_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SupplierReturn extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'supplier_returns';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'po_id',
        'grn_id',
        'return_number',
        'status',
        'return_date',
        'reason',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
