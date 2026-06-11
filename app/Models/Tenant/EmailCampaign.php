<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Email campaigns stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property string|null $subject
 * @property int|null $template_id
 * @property int|null $segment_id
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property string $status
 * @property array<string, mixed>|null $stats
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmailCampaign search(?string $search)
 */
class EmailCampaign extends TenantModel
{
    use HasFactory;

    protected $table = 'email_campaigns';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'subject',
        'template_id',
        'segment_id',
        'scheduled_at',
        'sent_at',
        'status',
        'stats',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'stats' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
            );
        });
    }
}
