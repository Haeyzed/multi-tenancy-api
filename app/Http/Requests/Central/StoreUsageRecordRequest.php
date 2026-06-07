<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new tenant usage record.
 */
class StoreUsageRecordRequest extends BaseRequest
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
             * UUID of the tenant whose usage is being recorded.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * UUID of the related subscription; nullable.
             *
             * @var string|null $subscription_id
             *
             * @example "660e8400-e29b-41d4-a716-446655440001"
             */
            'subscription_id' => 'nullable|uuid|exists:subscriptions,id',

            /**
             * Usage metric being tracked against plan limits.
             *
             * @var string $metric
             *
             * @example "api_calls"
             */
            'metric' => 'required|string|in:products,orders,storage,bandwidth,staff,transactions,api_calls',

            /**
             * Recorded quantity for the metric.
             *
             * @var float $quantity
             *
             * @example 8500
             */
            'quantity' => 'required|numeric|min:0',

            /**
             * Timestamp when the usage was measured.
             *
             * @var string $recorded_at
             *
             * @example "2026-01-15T10:30:00Z"
             */
            'recorded_at' => 'required|date',
        ];
    }
}
