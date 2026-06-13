<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Notification templates stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property string $channel
 * @property string|null $subject
 * @property string|null $body_html
 * @property string|null $body_text
 * @property array<string, mixed>|null $variables
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|NotificationTemplate search(?string $search)
 * @method static Builder|NotificationTemplate filterIsActive(array $statuses)
 */
class NotificationTemplate extends TenantModel
{
    use HasFactory;

    protected $table = 'notification_templates';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'channel',
        'subject',
        'body_html',
        'body_text',
        'variables',
        'is_active',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<NotificationTemplate> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<NotificationTemplate> $query
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

        $query->when($values !== [], fn (Builder $q): Builder => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
