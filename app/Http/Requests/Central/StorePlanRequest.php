<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new subscription plan.
 */
class StorePlanRequest extends FormRequest
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
             * Display name of the subscription plan.
             *
             * @var string $name
             *
             * @example "Pro Plan"
             */
            'name' => 'required|string|max:255',

            /**
             * URL-friendly unique identifier for the plan.
             *
             * @var string $slug
             *
             * @example "pro-plan"
             */
            'slug' => 'required|string|unique:plans,slug|max:255',

            /**
             * Marketing description of the plan; nullable.
             *
             * @var string|null $description
             *
             * @example "Best for growing businesses"
             */
            'description' => 'nullable|string',

            /**
             * Plan tier level used for upgrade/downgrade ordering.
             *
             * @var int $tier
             *
             * @example 2
             */
            'tier' => 'required|integer|min:1',

            /**
             * Whether the plan is available for new subscriptions; optional.
             *
             * @var bool $is_active
             *
             * @example true
             */
            'is_active' => 'sometimes|boolean',

            /**
             * Whether the plan is visible on public pricing pages; optional.
             *
             * @var bool $is_public
             *
             * @example true
             */
            'is_public' => 'sometimes|boolean',

            /**
             * Monthly price in the smallest currency unit.
             *
             * @var int $price_monthly
             *
             * @example 9900
             */
            'price_monthly' => 'required|integer|min:0',

            /**
             * Yearly price in the smallest currency unit.
             *
             * @var int $price_yearly
             *
             * @example 99000
             */
            'price_yearly' => 'required|integer|min:0',

            /**
             * ISO 4217 three-letter currency code.
             *
             * @var string $currency
             *
             * @example "USD"
             */
            'currency' => 'required|string|size:3',

            /**
             * Number of free trial days included with the plan.
             *
             * @var int $trial_days
             *
             * @example 14
             */
            'trial_days' => 'required|integer|min:0',

            /**
             * Display order on pricing pages; optional.
             *
             * @var int $sort_order
             *
             * @example 1
             */
            'sort_order' => 'sometimes|integer|min:0',

            /**
             * Feature limits and flags keyed by feature name.
             *
             * @var array<string, mixed> $features
             *
             * @example {"max_products": 1000, "api_access": true}
             */
            'features' => 'required|array',
        ];
    }
}
