<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new plan feature definition.
 */
class StorePlanFeatureRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * UUID of the plan this feature belongs to.
             *
             * @var string $plan_id
             *
             * @example "880e8400-e29b-41d4-a716-446655440003"
             */
            'plan_id' => 'required|uuid|exists:plans,id',

            /**
             * Machine-readable key identifying the feature.
             *
             * @var string $feature_key
             *
             * @example "max_products"
             */
            'feature_key' => 'required|string|max:255',

            /**
             * Stored value for the feature as a string representation.
             *
             * @var string $feature_value
             *
             * @example "1000"
             */
            'feature_value' => 'required|string|max:255',

            /**
             * Data type used to interpret the feature value.
             *
             * @var string $feature_type
             *
             * @example "integer"
             */
            'feature_type' => 'required|string|in:boolean,integer,string,decimal',
        ];
    }
}
