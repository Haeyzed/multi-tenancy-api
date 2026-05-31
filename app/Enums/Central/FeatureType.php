<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\PlanFeature;

/**
 * Data type of a structured plan feature value.
 *
 * Stored on {@see PlanFeature::$feature_type} in the `plan_features` table.
 */
enum FeatureType: string implements HasLabel
{
    use InteractsWithEnum;

    case Boolean = 'boolean';
    case Integer = 'integer';
    case String = 'string';
    case Decimal = 'decimal';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Boolean => 'Boolean',
            self::Integer => 'Integer',
            self::String => 'String',
            self::Decimal => 'Decimal',
        };
    }
}
