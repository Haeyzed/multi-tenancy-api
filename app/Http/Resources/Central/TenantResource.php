<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tenant
 */
class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * Display name of the tenant.
             *
             * @example "Acme Corp"
             */
            'name' => $this->name,

            /**
             * URL-friendly unique slug.
             *
             * @example "acme-corp"
             */
            'slug' => $this->slug,

            /**
             * Tenant database name.
             *
             * @example "tenant_acme_corp"
             */
            'database' => $this->database,

            /**
             * Primary domain hostname.
             *
             * @example "acme.example.com"
             */
            'domain' => $this->domain,

            /**
             * Current lifecycle status of the tenant.
             *
             * @example "active"
             */
            'status' => $this->status,

            /**
             * Identifier of the subscribed plan.
             *
             * @example 2
             *
             * @default null
             */
            'plan_id' => $this->plan_id,

            /**
             * Billing cycle for the subscription.
             *
             * @example "monthly"
             *
             * @default null
             */
            'billing_cycle' => $this->billing_cycle,

            /**
             * Timestamp when the trial period ends.
             *
             * @example "2026-02-01T00:00:00+00:00"
             *
             * @default null
             */
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),

            /**
             * Timestamp when the tenant first subscribed.
             *
             * @example "2026-01-15T10:30:00+00:00"
             *
             * @default null
             */
            'subscribed_at' => $this->subscribed_at?->toIso8601String(),

            /**
             * Timestamp when the subscription expires.
             *
             * @example "2027-01-15T10:30:00+00:00"
             *
             * @default null
             */
            'expires_at' => $this->expires_at?->toIso8601String(),

            /**
             * Email address of the tenant owner.
             *
             * @example "owner@acme.com"
             */
            'owner_email' => $this->owner_email,

            /**
             * Name of the tenant owner.
             *
             * @example "John Doe"
             */
            'owner_name' => $this->owner_name,

            /**
             * Tenant-specific settings as key-value pairs.
             *
             * @example {"timezone":"UTC"}
             *
             * @default null
             */
            'settings' => $this->settings,

            /**
             * Additional metadata for the tenant.
             *
             * @example {"industry":"retail"}
             *
             * @default null
             */
            'meta' => $this->meta,

            /**
             * Stancl tenancy internal data payload.
             *
             * @example {}
             *
             * @default null
             */
            'data' => $this->data,

            /**
             * Timestamp when the tenant was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the tenant was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Timestamp when the tenant was soft deleted.
             *
             * @example "2026-02-01T12:00:00+00:00"
             *
             * @default null
             */
            'deleted_at' => $this->deleted_at?->toIso8601String(),

            /**
             * Subscribed plan when eager loaded.
             *
             * @default null
             */
            'plan' => new PlanResource($this->whenLoaded('plan')),

            /**
             * Domains associated with the tenant when eager loaded.
             *
             * @default null
             */
            'domains' => DomainResource::collection($this->whenLoaded('domains')),

            /**
             * Key-value configuration entries when eager loaded.
             *
             * @default null
             */
            'configurations' => TenantConfigResource::collection($this->whenLoaded('configurations')),

            /**
             * Impersonation tokens for the tenant when eager loaded.
             *
             * @default null
             */
            'impersonation_tokens' => TenantImpersonationTokenResource::collection($this->whenLoaded('impersonationTokens')),

            /**
             * Subscriptions for the tenant when eager loaded.
             *
             * @default null
             */
            'subscriptions' => SubscriptionResource::collection($this->whenLoaded('subscriptions')),

            /**
             * Invoices for the tenant when eager loaded.
             *
             * @default null
             */
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),

            /**
             * Payments for the tenant when eager loaded.
             *
             * @default null
             */
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),

            /**
             * Saved payment methods when eager loaded.
             *
             * @default null
             */
            'payment_methods' => PaymentMethodResource::collection($this->whenLoaded('paymentMethods')),

            /**
             * Usage records when eager loaded.
             *
             * @default null
             */
            'usage_records' => UsageRecordResource::collection($this->whenLoaded('usageRecords')),

            /**
             * Support tickets when eager loaded.
             *
             * @default null
             */
            'support_tickets' => TenantSupportTicketResource::collection($this->whenLoaded('supportTickets')),

            /**
             * API keys when eager loaded.
             *
             * @default null
             */
            'api_keys' => ApiKeyResource::collection($this->whenLoaded('apiKeys')),

            /**
             * Health check results when eager loaded.
             *
             * @default null
             */
            'health_checks' => TenantHealthCheckResource::collection($this->whenLoaded('healthChecks')),

            /**
             * Daily aggregated metrics when eager loaded.
             *
             * @default null
             */
            'daily_metrics' => TenantMetricResource::collection($this->whenLoaded('dailyMetrics')),

            /**
             * Error logs when eager loaded.
             *
             * @default null
             */
            'error_logs' => ErrorLogResource::collection($this->whenLoaded('errorLogs')),
        ];
    }
}
