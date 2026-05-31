<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

interface HasLabel
{
    /**
     * Human-readable label for display in the UI.
     */
    public function label(): string;
}
