<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Consent records stored in the tenant database.
 * @property int $id
 * @property string $user_id
 * @property string $consent_type
 * @property string|null $version
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $given_at
 * @property Carbon|null $withdrawn_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ConsentRecord extends TenantModel
{
    use HasFactory;

    protected $table = 'consent_records';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'consent_type',
        'version',
        'ip_address',
        'user_agent',
        'given_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'given_at' => 'datetime',
            'withdrawn_at' => 'datetime',
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
