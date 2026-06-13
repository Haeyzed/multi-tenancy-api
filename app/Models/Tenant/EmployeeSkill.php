<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee skill stored in the tenant database.
 *
 * @property int $id
 * @property int $employee_id
 * @property string|null $skill_name
 * @property int $proficiency
 * @property bool $is_certified
 * @property string|null $certification_body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EmployeeSkill extends TenantModel
{
    use HasFactory;

    protected $table = 'employee_skills';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'skill_name',
        'proficiency',
        'is_certified',
        'certification_body',
    ];

    /**
     * Employee this skill belongs to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_certified' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
