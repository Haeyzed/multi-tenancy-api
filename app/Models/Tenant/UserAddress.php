<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * User addresses stored in the tenant database.
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string|null $label
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $company
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property string|null $phone
 * @property bool $is_default
 * @property string|null $lat
 * @property string|null $lng
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class UserAddress extends TenantModel
{
    use HasFactory;

    protected $table = 'user_addresses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'label',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'is_default',
        'lat',
        'lng',
    ];

    /**
     * Related User.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'lat' => 'decimal:2',
            'lng' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
