<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\UsageMetric;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Point-in-time usage measurement for a tenant or subscription.
 *
 * @property int $id
 * @property string $tenant_id
 * @property string|null $subscription_id
 * @property UsageMetric $metric
 * @property string $quantity
 * @property Carbon $recorded_at
 */
class UsageRecord extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'metric',
        'quantity',
        'recorded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metric' => UsageMetric::class,
            'quantity' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Tenant this usage record belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Subscription this usage record is scoped to, if any.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
