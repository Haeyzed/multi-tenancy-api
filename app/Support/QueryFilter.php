<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Helpers for parsing list-style query parameters (comma-separated or array).
 */
final class QueryFilter
{
    /**
     * @return list<string>
     */
    public static function parseList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                static fn (mixed $item): string => trim((string) $item),
                $value,
            ), static fn (string $item): bool => $item !== ''));
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            trim(...),
            explode(',', $value),
        ), static fn (string $item): bool => $item !== ''));
    }

    /**
     * Map active/inactive string tokens to boolean values for whereIn filters.
     *
     * @param  list<string>  $statuses
     * @return list<bool>
     */
    public static function booleanStatuses(array $statuses): array
    {
        $values = [];

        foreach ($statuses as $status) {
            $values[] = match ($status) {
                'active' => true,
                'inactive' => false,
                default => null,
            };
        }

        return array_values(array_unique(array_filter(
            $values,
            static fn (?bool $value): bool => $value !== null,
        )));
    }

    /**
     * Map public/private string tokens to boolean values for whereIn filters.
     *
     * @param  list<string>  $values
     * @return list<bool>
     */
    public static function booleanVisibility(array $values): array
    {
        $mapped = [];

        foreach ($values as $value) {
            $mapped[] = match ($value) {
                'public' => true,
                'private' => false,
                default => null,
            };
        }

        return array_values(array_unique(array_filter(
            $mapped,
            static fn (?bool $value): bool => $value !== null,
        )));
    }

    /**
     * Map verified/unverified string tokens to boolean values for whereIn filters.
     *
     * @param  list<string>  $values
     * @return list<bool>
     */
    public static function booleanVerified(array $values): array
    {
        $mapped = [];

        foreach ($values as $value) {
            $mapped[] = match ($value) {
                'verified' => true,
                'unverified' => false,
                default => null,
            };
        }

        return array_values(array_unique(array_filter(
            $mapped,
            static fn (?bool $value): bool => $value !== null,
        )));
    }
}
