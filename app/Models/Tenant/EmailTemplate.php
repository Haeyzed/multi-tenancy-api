<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Email templates stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property string $type
 * @property string|null $subject
 * @property string|null $body_html
 * @property string|null $body_text
 * @property array<string, mixed>|null $variables
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmailTemplate search(?string $search)
 */
class EmailTemplate extends TenantModel
{
    use HasFactory;

    protected $table = 'email_templates';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'subject',
        'body_html',
        'body_text',
        'variables',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
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
                $inner->where('name', 'like', "%{$search}%")
            );
        });
    }
}
