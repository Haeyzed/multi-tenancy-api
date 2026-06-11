<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Support tickets stored in the tenant database.
 * @property string $id
 * @property string|null $ticket_number
 * @property string|null $user_id
 * @property string|null $email
 * @property string|null $subject
 * @property string $category
 * @property string $priority
 * @property string $status
 * @property string|null $assigned_to
 * @property string|null $order_id
 * @property string|null $product_id
 * @property string $source
 * @property int|null $satisfaction_rating
 * @property Carbon|null $resolved_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|SupportTicket search(?string $search)
 */
class SupportTicket extends TenantModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'support_tickets';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_number',
        'user_id',
        'email',
        'subject',
        'category',
        'priority',
        'status',
        'assigned_to',
        'order_id',
        'product_id',
        'source',
        'satisfaction_rating',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Related User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Related Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Related Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('email', 'like', "%{$search}%")
            );
        });
    }
}
