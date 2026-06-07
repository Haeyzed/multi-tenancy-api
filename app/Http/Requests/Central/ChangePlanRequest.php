<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates subscription plan change payload.
 */
class ChangePlanRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Target plan UUID.
             *
             * @var string $plan_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'plan_id' => 'required|uuid|exists:plans,id',
        ];
    }
}
