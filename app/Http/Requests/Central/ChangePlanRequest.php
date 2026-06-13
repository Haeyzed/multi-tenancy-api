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
             * Target plan identifier.
             *
             * @var int $plan_id
             *
             * @example 1
             */
            'plan_id' => 'required|integer|exists:plans,id',
        ];
    }
}
