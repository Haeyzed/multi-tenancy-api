<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base form request for central API endpoints with a consistent validation error shape.
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator Validator instance with failed rules.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $this->validationFailedMessage($validator),
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Human-readable message for validation failures.
     */
    protected function validationFailedMessage(Validator $validator): string
    {
        return 'The given data was invalid.';
    }
}
