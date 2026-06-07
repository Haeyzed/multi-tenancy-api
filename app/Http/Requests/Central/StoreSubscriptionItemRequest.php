<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new subscription line item.
 */
class StoreSubscriptionItemRequest extends BaseRequest
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
             * UUID of the parent subscription.
             *
             * @var string $subscription_id
             *
             * @example "660e8400-e29b-41d4-a716-446655440001"
             */
            'subscription_id' => 'required|uuid|exists:subscriptions,id',

            /**
             * UUID of the plan billed by this line item.
             *
             * @var string $plan_id
             *
             * @example "880e8400-e29b-41d4-a716-446655440003"
             */
            'plan_id' => 'required|uuid|exists:plans,id',

            /**
             * Number of units subscribed.
             *
             * @var int $quantity
             *
             * @example 1
             */
            'quantity' => 'required|integer|min:1',

            /**
             * Price per unit in the smallest currency unit.
             *
             * @var int $unit_price
             *
             * @example 9900
             */
            'unit_price' => 'required|integer|min:0',

            /**
             * Total price for this line item in the smallest currency unit.
             *
             * @var int $total_price
             *
             * @example 9900
             */
            'total_price' => 'required|integer|min:0',
        ];
    }
}
