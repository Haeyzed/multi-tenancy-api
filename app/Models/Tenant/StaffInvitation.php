<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Staff invitations stored in the tenant database.
 *
 * @property int $id
 * @property string|null $email
 * @property int $role_id
 * @property string $invited_by
 * @property string|null $token
 * @property Carbon|null $expires_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|StaffInvitation search(?string $search)
 */
class StaffInvitation extends TenantModel
{
    use HasFactory;

    protected $table = 'staff_invitations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'role_id',
        'invited_by',
        'token',
        'expires_at',
        'accepted_at',
    ];

    /**
     * Related Role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('email', 'like', "%{$search}%");
            });
        });
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
