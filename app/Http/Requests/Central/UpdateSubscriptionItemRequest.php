<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing subscription line item.
 */
class UpdateSubscriptionItemRequest extends BaseRequest
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
             * UUID of the parent subscription; optional on update.
             *
             * @var string $subscription_id
             *
             * @example "660e8400-e29b-41d4-a716-446655440001"
             */
            'subscription_id' => 'sometimes|uuid|exists:subscriptions,id',

            /**
             * UUID of the plan billed by this line item; optional on update.
             *
             * @var string $plan_id
             *
             * @example "880e8400-e29b-41d4-a716-446655440003"
             */
            'plan_id' => 'sometimes|uuid|exists:plans,id',

            /**
             * Number of units subscribed; optional on update.
             *
             * @var int $quantity
             *
             * @example 3
             */
            'quantity' => 'sometimes|integer|min:1',

            /**
             * Price per unit in the smallest currency unit; optional on update.
             *
             * @var int $unit_price
             *
             * @example 9900
             */
            'unit_price' => 'sometimes|integer|min:0',

            /**
             * Total price for this line item in the smallest currency unit; optional on update.
             *
             * @var int $total_price
             *
             * @example 29700
             */
            'total_price' => 'sometimes|integer|min:0',
        ];
    }
}
