<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\PaymentMethodType;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment attempt or settlement against an invoice.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string|null $invoice_id
 * @property int $amount
 * @property string $currency
 * @property PaymentStatus $status
 * @property PaymentProvider $payment_provider
 * @property string|null $provider_payment_id
 * @property PaymentMethodType|null $payment_method_type
 * @property string|null $payment_method_last4
 * @property string|null $failure_message
 * @property int $refunded_amount
 */
class Payment extends Model
{
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'amount',
        'currency',
        'status',
        'payment_provider',
        'provider_payment_id',
        'payment_method_type',
        'payment_method_last4',
        'failure_message',
        'refunded_amount',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'payment_provider' => PaymentProvider::class,
            'payment_method_type' => PaymentMethodType::class,
        ];
    }

    /**
     * Tenant that made this payment.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Invoice settled by this payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
