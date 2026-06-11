<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Base validation for bulk delete payloads.
 */
abstract class BulkDeleteRequest extends BaseRequest
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
             * Record identifiers to delete in a single request.
             *
             * @var list<int|string> $ids
             *
             * @example [1, 2, 3]
             */
            'ids' => 'required|array|min:1',

            /**
             * Single record identifier within the bulk delete payload.
             *
             * @var int|string $id
             *
             * @example 1
             */
            'ids.*' => $this->idRule(),
        ];
    }

    /**
     * Validation rule applied to each entry in {@see ids}.
     */
    abstract protected function idRule(): string;
}
