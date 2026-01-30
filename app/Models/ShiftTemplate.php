<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'shift_type',
        'start_time',
        'end_time',
        'break_duration',
        'total_hours',
        'department_id',
        'branch_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'break_duration' => 'integer',
            'total_hours' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the company that owns the shift template.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the department for the shift template.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the branch for the shift template.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the schedules for the shift template.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class, 'template_id');
    }
}
