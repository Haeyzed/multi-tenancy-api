<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Audit logs stored in the tenant database.
 * @property string $id
 * @property string|null $auditable_type
 * @property string $auditable_id
 * @property string $action
 * @property string|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $url
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AuditLog extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'audit_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'old_values',
        'new_values',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
