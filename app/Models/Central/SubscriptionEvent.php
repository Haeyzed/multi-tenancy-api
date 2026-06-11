<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\SubscriptionEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit event describing a change in subscription state.
 *
 * @property int $id
 * @property string $subscription_id
 * @property SubscriptionEventType $event_type
 * @property string|null $from_plan_id
 * @property string|null $to_plan_id
 * @property EventTriggeredBy $triggered_by
 * @property array<string, mixed>|null $metadata
 */
class SubscriptionEvent extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscription_id',
        'event_type',
        'from_plan_id',
        'to_plan_id',
        'triggered_by',
        'metadata',
    ];

    /**
     * Subscription this event was recorded for.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Previous plan before the subscription changed.
     */
    public function fromPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'from_plan_id');
    }

    /**
     * New plan after the subscription changed.
     */
    public function toPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'to_plan_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => SubscriptionEventType::class,
            'triggered_by' => EventTriggeredBy::class,
            'metadata' => 'array',
        ];
    }
}
