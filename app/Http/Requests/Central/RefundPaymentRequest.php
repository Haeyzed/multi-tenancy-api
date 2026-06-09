<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates a payment refund request.
 */
class RefundPaymentRequest extends BaseRequest
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
             * Refund amount in smallest currency unit. Defaults to full payment amount.
             *
             * @var int $amount
             */
            'amount' => 'sometimes|integer|min:1',
        ];
    }
}
