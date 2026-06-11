<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Search queries stored in the tenant database.
 *
 * @property int $id
 * @property string|null $query
 * @property int $results_count
 * @property string|null $clicked_product_id
 * @property string|null $user_id
 * @property Carbon|null $occurred_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SearchQuery extends TenantModel
{
    use HasFactory;

    protected $table = 'search_queries';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'query',
        'results_count',
        'clicked_product_id',
        'user_id',
        'occurred_at',
    ];

    /**
     * Related User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
