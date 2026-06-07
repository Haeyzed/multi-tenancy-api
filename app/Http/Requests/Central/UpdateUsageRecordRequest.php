<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing tenant usage record.
 */
class UpdateUsageRecordRequest extends BaseRequest
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
             * UUID of the tenant whose usage is being recorded; optional on update.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * UUID of the related subscription; nullable.
             *
             * @var string|null $subscription_id
             *
             * @example "660e8400-e29b-41d4-a716-446655440001"
             */
            'subscription_id' => 'nullable|uuid|exists:subscriptions,id',

            /**
             * Usage metric being tracked against plan limits; optional on update.
             *
             * @var string $metric
             *
             * @example "storage"
             */
            'metric' => 'sometimes|string|in:products,orders,storage,bandwidth,staff,transactions,api_calls',

            /**
             * Recorded quantity for the metric; optional on update.
             *
             * @var float $quantity
             *
             * @example 512.5
             */
            'quantity' => 'sometimes|numeric|min:0',

            /**
             * Timestamp when the usage was measured; optional on update.
             *
             * @var string $recorded_at
             *
             * @example "2026-01-15T10:30:00Z"
             */
            'recorded_at' => 'sometimes|date',
        ];
    }
}
