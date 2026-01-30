<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'schedule_id',
        'employee_id',
        'shift_date',
        'start_time',
        'end_time',
        'shift_type',
        'total_hours',
        'is_overtime',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'total_hours' => 'decimal:2',
            'is_overtime' => 'boolean',
        ];
    }

    /**
     * Get the schedule that owns the assignment.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class, 'schedule_id');
    }

    /**
     * Get the employee for the assignment.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
