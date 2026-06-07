<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates checkout retry payload for pending signups.
 */
class SignupCheckoutRequest extends BaseRequest
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
             * Payment provider to create a new checkout session with.
             *
             * @var string $payment_provider
             *
             * @example "stripe"
             */
            'payment_provider' => 'required|string|in:stripe,paystack',

            /**
             * Redirect URL after successful payment.
             *
             * @var string $success_url
             *
             * @example "https://app.example.com/signup/success"
             */
            'success_url' => 'sometimes|url',

            /**
             * Redirect URL when payment is cancelled.
             *
             * @var string $cancel_url
             *
             * @example "https://app.example.com/signup/cancel"
             */
            'cancel_url' => 'sometimes|url',
        ];
    }
}
