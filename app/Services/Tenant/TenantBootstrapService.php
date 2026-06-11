<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Central\Tenant;
use App\Models\Tenant\GeneralSetting;
use App\Models\Tenant\Store;
use Illuminate\Support\Str;

/**
 * Idempotent bootstrap of tenant-wide defaults after database creation or onboarding.
 */
class TenantBootstrapService
{
    /**
     * @var array<string, mixed>
     */
    private const GENERAL_DEFAULTS = [
        'currency_position' => 'before',
        'default_weight_unit' => 'kg',
        'default_dimension_unit' => 'cm',
    ];

    /**
     * @var array<string, string>
     */
    private const CURRENCY_SYMBOLS = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'NGN' => '₦',
        'CAD' => 'CA$',
        'AUD' => 'A$',
    ];

    public function __construct(
        private readonly StoreService $storeService,
    ) {}

    /**
     * Seed general settings and the primary store from the central tenant record.
     */
    public function bootstrap(?Tenant $centralTenant = null): void
    {
        $centralTenant ??= tenant();

        if (! $centralTenant instanceof Tenant) {
            return;
        }

        $this->ensureGeneralSettings($centralTenant);
        $this->ensurePrimaryStore($centralTenant);
    }

    /**
     * Public branding payload for unauthenticated clients (login screen, etc.).
     *
     * @return array<string, mixed>
     */
    public function getPublicPayload(): array
    {
        /** @var Tenant $centralTenant */
        $centralTenant = tenant();

        $general = app(GeneralSettingService::class)->get();

        $primaryStore = Store::query()
            ->where('is_primary', true)
            ->with(['logoMedia', 'faviconMedia'])
            ->first();

        return [
            'tenant' => [
                'id' => $centralTenant->id,
                'name' => $centralTenant->name,
                'slug' => $centralTenant->slug,
            ],
            'branding' => [
                'company_name' => $general->company_name ?? $centralTenant->name,
                'legal_name' => $general->legal_name,
                'support_email' => $general->support_email,
                'support_phone' => $general->support_phone,
                'support_whatsapp' => $general->support_whatsapp,
                'website_url' => $general->website_url,
                'default_currency' => $general->default_currency,
                'currency_symbol' => $general->currency_symbol,
                'currency_position' => $general->currency_position,
                'default_timezone' => $general->default_timezone,
                'default_language' => $general->default_language,
                'social_links' => $general->social_links,
                'privacy_policy_url' => $general->privacy_policy_url,
                'terms_of_service_url' => $general->terms_of_service_url,
                'refund_policy_url' => $general->refund_policy_url,
            ],
            'primary_store' => $primaryStore === null ? null : [
                'id' => $primaryStore->id,
                'name' => $primaryStore->name,
                'slug' => $primaryStore->slug,
                'tagline' => $primaryStore->tagline,
                'logo_url' => $primaryStore->logoMedia?->url,
                'favicon_url' => $primaryStore->faviconMedia?->url,
            ],
        ];
    }

    private function ensureGeneralSettings(Tenant $centralTenant): void
    {
        $tenantSettings = is_array($centralTenant->settings) ? $centralTenant->settings : [];
        $currency = (string) ($tenantSettings['currency'] ?? 'USD');

        $payload = array_merge(self::GENERAL_DEFAULTS, [
            'company_name' => $centralTenant->name,
            'legal_name' => $centralTenant->name,
            'support_email' => $centralTenant->owner_email,
            'billing_email' => $centralTenant->owner_email,
            'email_from_name' => $centralTenant->name,
            'email_from_address' => $centralTenant->owner_email,
            'default_currency' => $currency,
            'currency_symbol' => self::CURRENCY_SYMBOLS[$currency] ?? '$',
            'default_timezone' => (string) ($tenantSettings['timezone'] ?? 'UTC'),
            'default_language' => (string) ($tenantSettings['locale'] ?? 'en'),
        ]);

        $existing = GeneralSetting::query()->first();

        if ($existing === null) {
            GeneralSetting::query()->create($payload);

            return;
        }

        $existing->fill(array_filter([
            'company_name' => $existing->company_name ?: $payload['company_name'],
            'legal_name' => $existing->legal_name ?: $payload['legal_name'],
            'support_email' => $existing->support_email ?: $payload['support_email'],
            'billing_email' => $existing->billing_email ?: $payload['billing_email'],
            'email_from_name' => $existing->email_from_name ?: $payload['email_from_name'],
            'email_from_address' => $existing->email_from_address ?: $payload['email_from_address'],
        ]))->save();
    }

    private function ensurePrimaryStore(Tenant $centralTenant): void
    {
        if (Store::query()->exists()) {
            return;
        }

        $this->storeService->create([
            'name' => $centralTenant->name,
            'slug' => $this->uniqueStoreSlug($centralTenant->slug),
            'code' => Str::upper(Str::substr($centralTenant->slug, 0, 12)),
            'type' => 'online',
            'email' => $centralTenant->owner_email,
            'timezone' => is_array($centralTenant->settings)
                ? ($centralTenant->settings['timezone'] ?? 'UTC')
                : 'UTC',
            'currency' => is_array($centralTenant->settings)
                ? ($centralTenant->settings['currency'] ?? 'USD')
                : 'USD',
            'is_primary' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function uniqueStoreSlug(string $slug): string
    {
        $candidate = $slug;
        $suffix = 1;

        while (Store::query()->where('slug', $candidate)->exists()) {
            $candidate = "{$slug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
