<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for updating an existing subscription plan.
 */
class UpdatePlanRequest extends FormRequest
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
             * Display name of the subscription plan; optional on update.
             *
             * @var string $name
             *
             * @example "Pro Plan"
             */
            'name' => 'sometimes|string|max:255',

            /**
             * URL-friendly unique identifier for the plan; optional on update.
             *
             * @var string $slug
             *
             * @example "pro-plan"
             */
            'slug' => 'sometimes|string|unique:plans,slug|max:255',

            /**
             * Marketing description of the plan; nullable.
             *
             * @var string|null $description
             *
             * @example "Best for growing businesses"
             */
            'description' => 'nullable|string',

            /**
             * Plan tier level used for upgrade/downgrade ordering; optional on update.
             *
             * @var int $tier
             *
             * @example 3
             */
            'tier' => 'sometimes|integer|min:1',

            /**
             * Whether the plan is available for new subscriptions; optional.
             *
             * @var bool $is_active
             *
             * @example false
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
             * Monthly price in the smallest currency unit; optional on update.
             *
             * @var int $price_monthly
             *
             * @example 12900
             */
            'price_monthly' => 'sometimes|integer|min:0',

            /**
             * Yearly price in the smallest currency unit; optional on update.
             *
             * @var int $price_yearly
             *
             * @example 129000
             */
            'price_yearly' => 'sometimes|integer|min:0',

            /**
             * ISO 4217 three-letter currency code; optional on update.
             *
             * @var string $currency
             *
             * @example "USD"
             */
            'currency' => 'sometimes|string|size:3',

            /**
             * Number of free trial days included with the plan; optional on update.
             *
             * @var int $trial_days
             *
             * @example 7
             */
            'trial_days' => 'sometimes|integer|min:0',

            /**
             * Display order on pricing pages; optional.
             *
             * @var int $sort_order
             *
             * @example 2
             */
            'sort_order' => 'sometimes|integer|min:0',

            /**
             * Feature limits and flags keyed by feature name; optional on update.
             *
             * @var array<string, mixed> $features
             *
             * @example {"max_products": 5000, "api_access": true}
             */
            'features' => 'sometimes|array',
        ];
    }
}
