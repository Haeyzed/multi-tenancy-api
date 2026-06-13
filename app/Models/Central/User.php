<?php

declare(strict_types=1);

namespace App\Models\Central;

use Database\Factories\Central\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * Platform administrator account stored in the central database.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property Carbon|null $last_login_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|User search(?string $search)
 * @method static Builder|User filterIsActive(array $statuses)
 * @method static Builder|User filterTrashed(array $tokens)
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use CentralConnection;
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'last_login_at',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Scope a query to search by name, email, role, or permission.
     *
     * @param Builder<User> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search): void {
            $q->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('roles', function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('permissions', function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<User> $query
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = [];

        foreach ($statuses as $status) {
            $values[] = match ($status) {
                'active' => true,
                'inactive' => false,
                default => null,
            };
        }

        $values = array_values(array_unique(array_filter(
            $values,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($values !== [], fn (Builder $q): Builder => $q->whereIn('is_active', $values));
    }

    /**
     * Filter by soft-delete visibility tokens (only, with).
     *
     * @param Builder<User> $query
     * @param list<string> $tokens
     */
    public function scopeFilterTrashed(Builder $query, array $tokens): void
    {
        if ($tokens === []) {
            return;
        }

        if (in_array('only', $tokens, true)) {
            $query->onlyTrashed();

            return;
        }

        if (in_array('with', $tokens, true)) {
            $query->withTrashed();
        }
    }

    /**
     * Support tickets currently assigned to this administrator.
     *
     * @return HasMany<TenantSupportTicket, $this>
     */
    public function assignedSupportTickets(): HasMany
    {
        return $this->hasMany(TenantSupportTicket::class, 'assigned_to');
    }

    /**
     * Impersonation tokens issued by this administrator.
     *
     * @return HasMany<TenantImpersonationToken, $this>
     */
    public function issuedImpersonationTokens(): HasMany
    {
        return $this->hasMany(TenantImpersonationToken::class, 'admin_id');
    }

    /**
     * Support messages sent by this administrator.
     *
     * @return HasMany<TenantSupportMessage, $this>
     */
    public function sentSupportMessages(): HasMany
    {
        return $this->hasMany(TenantSupportMessage::class, 'sender_id');
    }

    /**
     * In-app notifications for this administrator.
     *
     * @return HasMany<UserNotification, $this>
     */
    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Push notification device tokens registered by this administrator.
     *
     * @return HasMany<PushNotificationToken, $this>
     */
    public function pushNotificationTokens(): HasMany
    {
        return $this->hasMany(PushNotificationToken::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
