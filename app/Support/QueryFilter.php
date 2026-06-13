<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Helpers for parsing list-style query parameters (comma-separated or array).
 */
final class QueryFilter
{
    /**
     * Normalize comma-separated or array query values into a trimmed list.
     *
     * @return list<string>
     */
    public static function filterList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(
                $value,
                static fn (mixed $item): bool => $item !== '' && $item !== null,
            ));
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    /**
     * @return list<string>
     */
    public static function parseList(mixed $value): array
    {
        return self::filterList($value);
    }
}
