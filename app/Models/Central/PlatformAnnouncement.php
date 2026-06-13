<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\AnnouncementTargetAudience;
use App\Enums\Central\AnnouncementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Platform-wide announcement shown to tenants or administrators.
 *
 * @property int $id
 * @property string $title
 * @property string $body
 * @property AnnouncementType $type
 * @property AnnouncementTargetAudience $target_audience
 * @property array<string, mixed>|null $target_plans
 * @property bool $is_active
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 */
class PlatformAnnouncement extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'body',
        'type',
        'target_audience',
        'target_plans',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    /**
     * Scope a query to search by title or body.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
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

        $query->when($values !== [], fn(Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Filter by announcement type values.
     *
     * @param list<string> $types
     */
    public function scopeFilterType(Builder $query, array $types): void
    {
        $query->when($types !== [], fn(Builder $q) => $q->whereIn('type', $types));
    }

    /**
     * Filter by target audience values.
     *
     * @param list<string> $audiences
     */
    public function scopeFilterTargetAudience(Builder $query, array $audiences): void
    {
        $query->when($audiences !== [], fn(Builder $q) => $q->whereIn('target_audience', $audiences));
    }

    /**
     * Active announcements currently within their schedule window.
     */
    public function scopeCurrentlyLive(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AnnouncementType::class,
            'target_audience' => AnnouncementTargetAudience::class,
            'target_plans' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
