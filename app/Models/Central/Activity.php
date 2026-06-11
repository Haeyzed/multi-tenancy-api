<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * Activity log entry stored in the central database.
 */
class Activity extends SpatieActivity
{
    use CentralConnection;

    /**
     * Scope a query to search by description, log name, or event.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%");
            });
        });
    }

    /**
     * @param list<string> $values
     */
    public function scopeFilterLogName(Builder $query, array $values): void
    {
        $query->when($values !== [], fn(Builder $q) => $q->whereIn('log_name', $values));
    }

    /**
     * @param list<string> $values
     */
    public function scopeFilterEvent(Builder $query, array $values): void
    {
        $query->when($values !== [], fn(Builder $q) => $q->whereIn('event', $values));
    }
}
