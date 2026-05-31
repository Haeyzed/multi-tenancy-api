<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Tenant-scoped permission record.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Permission extends SpatiePermission
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'module',
    ];
}
