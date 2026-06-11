<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates updates to tenant-wide general settings.
 */
class UpdateGeneralSettingRequest extends BaseRequest
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
            /** @var string|null $company_name */
            'company_name' => 'sometimes|string|max:255',
            /** @var string|null $legal_name */
            'legal_name' => 'sometimes|string|max:255',
            /** @var string|null $support_email */
            'support_email' => 'nullable|email|max:255',
            /** @var string|null $support_phone */
            'support_phone' => 'nullable|string|max:50',
            /** @var string|null $support_whatsapp */
            'support_whatsapp' => 'nullable|string|max:50',
            /** @var string|null $billing_email */
            'billing_email' => 'nullable|email|max:255',
            /** @var string|null $tax_id */
            'tax_id' => 'nullable|string|max:100',
            /** @var string|null $registration_number */
            'registration_number' => 'nullable|string|max:100',
            /** @var array<string, mixed>|null $headquarters_address */
            'headquarters_address' => 'nullable|array',
            /** @var string|null $website_url */
            'website_url' => 'nullable|url|max:255',
            /** @var string $default_currency */
            'default_currency' => 'sometimes|string|size:3',
            /** @var string $currency_symbol */
            'currency_symbol' => 'sometimes|string|max:5',
            /** @var string $currency_position */
            'currency_position' => 'sometimes|in:before,after',
            /** @var string $default_timezone */
            'default_timezone' => 'sometimes|string|timezone',
            /** @var string $default_language */
            'default_language' => 'sometimes|string|max:10',
            /** @var string $default_weight_unit */
            'default_weight_unit' => 'sometimes|in:kg,g,lb,oz',
            /** @var string $default_dimension_unit */
            'default_dimension_unit' => 'sometimes|in:cm,m,in,ft',
            /** @var string|null $email_from_name */
            'email_from_name' => 'nullable|string|max:255',
            /** @var string|null $email_from_address */
            'email_from_address' => 'nullable|email|max:255',
            /** @var string|null $industry */
            'industry' => 'nullable|string|max:100',
            /** @var string|null $business_type */
            'business_type' => 'nullable|string|max:100',
            /** @var array<string, mixed>|null $social_links */
            'social_links' => 'nullable|array',
            /** @var string|null $privacy_policy_url */
            'privacy_policy_url' => 'nullable|url|max:255',
            /** @var string|null $terms_of_service_url */
            'terms_of_service_url' => 'nullable|url|max:255',
            /** @var string|null $refund_policy_url */
            'refund_policy_url' => 'nullable|url|max:255',
        ];
    }
}
