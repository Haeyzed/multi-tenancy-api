<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for updating an existing subscription lifecycle event.
 */
class UpdateSubscriptionEventRequest extends FormRequest
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
             * UUID of the subscription this event belongs to; optional on update.
             *
             * @var string $subscription_id
             *
             * @example "660e8400-e29b-41d4-a716-446655440001"
             */
            'subscription_id' => 'sometimes|uuid|exists:subscriptions,id',

            /**
             * Type of subscription lifecycle event recorded; optional on update.
             *
             * @var string $event_type
             *
             * @example "renewed"
             */
            'event_type' => 'sometimes|string|in:upgraded,downgraded,renewed,cancelled,reactivated,trial_ended,payment_failed',

            /**
             * UUID of the plan before the change; nullable.
             *
             * @var string|null $from_plan_id
             *
             * @example "880e8400-e29b-41d4-a716-446655440003"
             */
            'from_plan_id' => 'nullable|uuid|exists:plans,id',

            /**
             * UUID of the plan after the change; nullable.
             *
             * @var string|null $to_plan_id
             *
             * @example "990e8400-e29b-41d4-a716-446655440004"
             */
            'to_plan_id' => 'nullable|uuid|exists:plans,id',

            /**
             * Actor or system that triggered this event; optional on update.
             *
             * @var string $triggered_by
             *
             * @example "admin"
             */
            'triggered_by' => 'sometimes|string|in:user,system,admin,payment',

            /**
             * Additional event context and provider payloads; optional.
             *
             * @var array<string, mixed> $metadata
             *
             * @example {"note": "Manual renewal by support"}
             */
            'metadata' => 'sometimes|array',
        ];
    }
}
