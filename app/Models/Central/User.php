<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\UserRole;
use Database\Factories\Central\UserFactory;
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
 * Platform administrator account in the central database.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property UserRole $role
 * @property Carbon|null $last_login_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use CentralConnection, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Support tickets currently assigned to this administrator.
     */
    public function assignedSupportTickets(): HasMany
    {
        return $this->hasMany(TenantSupportTicket::class, 'assigned_to');
    }

    /**
     * Impersonation tokens issued by this administrator.
     */
    public function issuedImpersonationTokens(): HasMany
    {
        return $this->hasMany(TenantImpersonationToken::class, 'admin_id');
    }

    /**
     * Support messages sent by this administrator.
     */
    public function sentSupportMessages(): HasMany
    {
        return $this->hasMany(TenantSupportMessage::class, 'sender_id');
    }

    /**
     * In-app notifications for this administrator.
     */
    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Push notification device tokens registered by this administrator.
     */
    public function pushNotificationTokens(): HasMany
    {
        return $this->hasMany(PushNotificationToken::class);
    }
}
