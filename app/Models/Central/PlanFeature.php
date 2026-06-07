<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\FeatureType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Enforceable feature limit or flag on a plan (used for middleware and usage quotas).
 *
 * Display copy for pricing pages lives on {@see Plan::$features}, not here.
 *
 * @property int $id
 * @property string $plan_id
 * @property string $feature_key
 * @property string $feature_value
 * @property FeatureType $feature_type
 */
class PlanFeature extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_value',
        'feature_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'feature_type' => FeatureType::class,
        ];
    }

    /**
     * Plan this feature definition belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
