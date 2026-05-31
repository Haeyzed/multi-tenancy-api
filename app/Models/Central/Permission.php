<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * Platform permission scoped to a functional module.
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
    use CentralConnection;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'module',
    ];
}
