<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Testimonials stored in the tenant database.
 * @property int $id
 * @property string|null $author_name
 * @property string|null $author_title
 * @property string|null $content
 * @property int|null $rating
 * @property int|null $media_id
 * @property bool $is_featured
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Testimonial extends TenantModel
{
    use HasFactory;

    protected $table = 'testimonials';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'author_name',
        'author_title',
        'content',
        'rating',
        'media_id',
        'is_featured',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
