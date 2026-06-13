<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Enums\Tenant\OtpPurpose;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One-time password issued for tenant authentication flows.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $email
 * @property string $otp
 * @property OtpPurpose $purpose
 * @property Carbon $expires_at
 * @property Carbon|null $verified_at
 * @property string|null $verification_token
 * @property Carbon|null $verification_token_expires_at
 * @property int $attempts
 * @property int $resend_count
 * @property Carbon|null $last_sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Otp extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'otp',
        'purpose',
        'expires_at',
        'verified_at',
        'verification_token',
        'verification_token_expires_at',
        'attempts',
        'resend_count',
        'last_sent_at',
    ];

    /**
     * User associated with this OTP, when known.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the OTP has expired without being verified.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Whether the OTP has already been verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Whether the verification token is still valid.
     */
    public function hasValidVerificationToken(): bool
    {
        return $this->verification_token !== null
            && $this->verification_token_expires_at !== null
            && $this->verification_token_expires_at->isFuture();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => OtpPurpose::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'verification_token_expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }
}
