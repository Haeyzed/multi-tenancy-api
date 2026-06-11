<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Sms logs stored in the tenant database.
 * @property int $id
 * @property string|null $recipient
 * @property string|null $message
 * @property string $status
 * @property string|null $provider
 * @property string|null $provider_message_id
 * @property string|null $cost
 * @property Carbon|null $sent_at
 * @property Carbon|null $delivered_at
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SmsLog extends TenantModel
{
    use HasFactory;

    protected $table = 'sms_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'recipient',
        'message',
        'status',
        'provider',
        'provider_message_id',
        'cost',
        'sent_at',
        'delivered_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
