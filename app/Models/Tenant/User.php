<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Database\Factories\Tenant\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Store staff or customer account inside a tenant database.
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property string|null $phone
 * @property int|null $avatar_media_id
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property Carbon|null $birth_date
 * @property string|null $gender
 * @property string|null $locale
 * @property string|null $timezone
 * @property bool $is_active
 * @property bool $is_marketing_opt_in
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $name
 *
 * @method static Builder|User search(?string $search)
 * @method static Builder|User filterIsActive(array $statuses)
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $guard_name = 'tenant';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'avatar_media_id',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'last_login_ip',
        'birth_date',
        'gender',
        'locale',
        'timezone',
        'is_active',
        'is_marketing_opt_in',
        'notes',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Full display name derived from first and last name.
     *
     * @return BelongsTo<Media, $this>
     */
    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Avatar media file for this user.
     *
     * @return BelongsTo<Media, $this>
     */
    public function avatarMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_media_id');
    }

    /**
     * Scope a query to search by name or email.
     *
     * @param Builder<User> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search): void {
            $q->where(function (Builder $q) use ($search): void {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'is_marketing_opt_in' => 'boolean',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
