<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Performance review scores stored in the tenant database.
 *
 * @property int $id
 * @property string $review_id
 * @property int $criteria_id
 * @property string $score
 * @property string|null $comments
 * @property string $weight
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PerformanceReviewScore extends TenantModel
{
    use HasFactory;

    protected $table = 'performance_review_scores';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'review_id',
        'criteria_id',
        'score',
        'comments',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'weight' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
