<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Activity log extensions stored in the tenant database.
 * @property int $id
 * @property int $activity_log_id
 * @property string $event_category
 * @property string $business_impact
 * @property array<string, mixed>|null $notified_users
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ActivityLogExtension extends TenantModel
{
    use HasFactory;

    protected $table = 'activity_log_extensions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'activity_log_id',
        'event_category',
        'business_impact',
        'notified_users',
    ];

    protected function casts(): array
    {
        return [
            'notified_users' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
