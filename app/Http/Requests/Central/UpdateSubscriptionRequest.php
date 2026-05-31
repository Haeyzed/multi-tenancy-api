<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for updating an existing tenant subscription.
 */
class UpdateSubscriptionRequest extends FormRequest
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
             * UUID of the subscribing tenant; optional on update.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * UUID of the subscribed plan; optional on update.
             *
             * @var string $plan_id
             *
             * @example "880e8400-e29b-41d4-a716-446655440003"
             */
            'plan_id' => 'sometimes|uuid|exists:plans,id',

            /**
             * Current lifecycle status of the subscription; optional on update.
             *
             * @var string $status
             *
             * @example "cancelled"
             */
            'status' => 'sometimes|string|in:trialing,active,past_due,cancelled,paused,expired',

            /**
             * Recurring billing interval for the subscription; optional on update.
             *
             * @var string $billing_cycle
             *
             * @example "yearly"
             */
            'billing_cycle' => 'sometimes|string|in:monthly,yearly',

            /**
             * Start of the current billing period; optional on update.
             *
             * @var string $current_period_start
             *
             * @example "2026-01-01T00:00:00Z"
             */
            'current_period_start' => 'sometimes|date',

            /**
             * End of the current billing period; optional on update.
             *
             * @var string $current_period_end
             *
             * @example "2026-12-31T23:59:59Z"
             */
            'current_period_end' => 'sometimes|date',

            /**
             * When the trial period ends; nullable if not trialing.
             *
             * @var string|null $trial_ends_at
             *
             * @example "2026-01-15T00:00:00Z"
             */
            'trial_ends_at' => 'nullable|date',

            /**
             * When the subscription was cancelled; nullable.
             *
             * @var string|null $cancelled_at
             *
             * @example "2026-06-01T12:00:00Z"
             */
            'cancelled_at' => 'nullable|date',

            /**
             * Reason provided for cancellation; nullable.
             *
             * @var string|null $cancellation_reason
             *
             * @example "Switching to a competitor"
             */
            'cancellation_reason' => 'nullable|string',

            /**
             * Payment gateway managing this subscription; optional on update.
             *
             * @var string $payment_provider
             *
             * @example "paddle"
             */
            'payment_provider' => 'sometimes|string|in:stripe,paddle,paypal',

            /**
             * External subscription identifier from the provider; nullable.
             *
             * @var string|null $payment_provider_id
             *
             * @example "sub_1NxAbCdEfGhIjKlM"
             */
            'payment_provider_id' => 'nullable|string',

            /**
             * External payment method identifier; nullable.
             *
             * @var string|null $payment_method_id
             *
             * @example "pm_1NxAbCdEfGhIjKlM"
             */
            'payment_method_id' => 'nullable|string',

            /**
             * UUID of the most recent invoice; nullable.
             *
             * @var string|null $latest_invoice_id
             *
             * @example "770e8400-e29b-41d4-a716-446655440002"
             */
            'latest_invoice_id' => 'nullable|uuid|exists:invoices,id',
        ];
    }
}
