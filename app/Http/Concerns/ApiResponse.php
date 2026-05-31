<?php

declare(strict_types=1);

namespace App\Http\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Return a JSON payload with optional message and data.
     *
     * @param  mixed  $data  Resource, collection, or array payload.
     * @param  string|null  $message  Human-readable status message.
     * @param  int  $status  HTTP status code.
     */
    protected function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = [
            'success' => true,
        ];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        if ($data !== null) {
            $payload['data'] = $data instanceof JsonResource ? $data : $data;
        }

        return response()->json($payload, $status);
    }

    /**
     * Return a paginated JSON payload.
     *
     * @param  LengthAwarePaginator<int, mixed>  $paginator  Paginated query result.
     * @param  mixed  $data  Resource collection for the current page.
     */
    protected function paginated(LengthAwarePaginator $paginator, mixed $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Return a created resource response.
     *
     * @param  mixed  $data  Created resource payload.
     * @param  string  $message  Human-readable status message.
     */
    protected function created(mixed $data, string $message): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return an updated resource response.
     *
     * @param  mixed  $data  Updated resource payload.
     * @param  string  $message  Human-readable status message.
     */
    protected function updated(mixed $data, string $message): JsonResponse
    {
        return $this->success($data, $message);
    }

    /**
     * Return a deleted resource response.
     *
     * @param  string  $message  Human-readable status message.
     */
    protected function deleted(string $message): JsonResponse
    {
        return $this->success(message: $message);
    }
}
