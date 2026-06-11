<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Gdpr requests stored in the tenant database.
 * @property string $id
 * @property string $user_id
 * @property string $type
 * @property string $status
 * @property array<string, mixed>|null $request_data
 * @property array<string, mixed>|null $response_data
 * @property Carbon|null $completed_at
 * @property string|null $processed_by
 * @property string|null $rejection_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GdprRequest extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'gdpr_requests';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'request_data',
        'response_data',
        'completed_at',
        'processed_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'response_data' => 'array',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
