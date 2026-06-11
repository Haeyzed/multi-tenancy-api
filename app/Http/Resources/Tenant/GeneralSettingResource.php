<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GeneralSetting
 */
class GeneralSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example 1 */
            'id' => $this->id,
            /** @example "Acme Retail Group" */
            'company_name' => $this->company_name,
            /** @example "Acme Retail Group Ltd." */
            'legal_name' => $this->legal_name,
            /** @example "support@acme.example.com" */
            'support_email' => $this->support_email,
            'support_phone' => $this->support_phone,
            'support_whatsapp' => $this->support_whatsapp,
            'billing_email' => $this->billing_email,
            'tax_id' => $this->tax_id,
            'registration_number' => $this->registration_number,
            'headquarters_address' => $this->headquarters_address,
            'website_url' => $this->website_url,
            /** @example "USD" */
            'default_currency' => $this->default_currency,
            /** @example "$" */
            'currency_symbol' => $this->currency_symbol,
            /** @example "before" */
            'currency_position' => $this->currency_position,
            /** @example "UTC" */
            'default_timezone' => $this->default_timezone,
            /** @example "en" */
            'default_language' => $this->default_language,
            'default_weight_unit' => $this->default_weight_unit,
            'default_dimension_unit' => $this->default_dimension_unit,
            'email_from_name' => $this->email_from_name,
            'email_from_address' => $this->email_from_address,
            'industry' => $this->industry,
            'business_type' => $this->business_type,
            'social_links' => $this->social_links,
            'privacy_policy_url' => $this->privacy_policy_url,
            'terms_of_service_url' => $this->terms_of_service_url,
            'refund_policy_url' => $this->refund_policy_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
