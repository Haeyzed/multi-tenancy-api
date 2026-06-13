<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * GDPR data subject request stored in the tenant database.
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $status
 * @property array<string, mixed>|null $request_data
 * @property array<string, mixed>|null $response_data
 * @property Carbon|null $completed_at
 * @property string|null $processed_by
 * @property string|null $rejection_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|GdprRequest filterStatus(array $statuses)
 */
class GdprRequest extends TenantModel
{
    use HasFactory;

    protected $table = 'gdpr_requests';
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

    /**
     * User who submitted this request.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Filter by status values.
     *
     * @param list<string> $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn(Builder $q) => $q->whereIn('status', $statuses));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
}
