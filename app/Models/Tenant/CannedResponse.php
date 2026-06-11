<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Canned responses stored in the tenant database.
 * @property int $id
 * @property string|null $title
 * @property string|null $shortcut
 * @property string|null $body
 * @property string|null $category
 * @property bool $is_active
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CannedResponse search(?string $search)
 */
class CannedResponse extends TenantModel
{
    use HasFactory;

    protected $table = 'canned_responses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'shortcut',
        'body',
        'category',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
            );
        });
    }
}
