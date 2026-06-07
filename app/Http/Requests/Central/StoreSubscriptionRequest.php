<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new tenant subscription.
 */
class StoreSubscriptionRequest extends BaseRequest
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
             * UUID of the subscribing tenant.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * UUID of the subscribed plan.
             *
             * @var string $plan_id
             *
             * @example "880e8400-e29b-41d4-a716-446655440003"
             */
            'plan_id' => 'required|uuid|exists:plans,id',

            /**
             * Current lifecycle status of the subscription.
             *
             * @var string $status
             *
             * @example "active"
             */
            'status' => 'required|string|in:trialing,active,past_due,cancelled,paused,expired',

            /**
             * Recurring billing interval for the subscription.
             *
             * @var string $billing_cycle
             *
             * @example "monthly"
             */
            'billing_cycle' => 'required|string|in:monthly,yearly',

            /**
             * Start of the current billing period.
             *
             * @var string $current_period_start
             *
             * @example "2026-01-01T00:00:00Z"
             */
            'current_period_start' => 'required|date',

            /**
             * End of the current billing period.
             *
             * @var string $current_period_end
             *
             * @example "2026-01-31T23:59:59Z"
             */
            'current_period_end' => 'required|date',

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
             * Payment gateway managing this subscription.
             *
             * @var string $payment_provider
             *
             * @example "stripe"
             */
            'payment_provider' => 'required|string|in:stripe,paddle,paypal',

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
