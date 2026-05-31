<?php

declare(strict_types=1);

namespace Database\Seeders\Central\Concerns;

use App\Models\Central\Invoice;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Models\Central\User;

/**
 * Lookup helpers for resolving seeded central records by natural keys.
 */
trait InteractsWithCentralSeeders
{
    /**
     * Find a plan by its slug.
     */
    protected function plan(string $slug): Plan
    {
        return Plan::query()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Find a tenant by its slug.
     */
    protected function tenant(string $slug): Tenant
    {
        return Tenant::query()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Find a platform administrator by email.
     */
    protected function user(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }

    /**
     * Resolve a platform administrator primary key by email.
     */
    protected function userId(string $email): int
    {
        return $this->user($email)->id;
    }

    /**
     * Find the primary subscription for a tenant.
     */
    protected function subscription(string $tenantSlug): Subscription
    {
        return $this->tenant($tenantSlug)->subscriptions()->firstOrFail();
    }

    /**
     * Find an invoice by its invoice number.
     */
    protected function invoice(string $invoiceNumber): Invoice
    {
        return Invoice::query()->where('invoice_number', $invoiceNumber)->firstOrFail();
    }
}
