<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'week_start_date',
        'week_end_date',
        'template_id',
        'status',
        'created_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'week_end_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the company that owns the shift schedule.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the template for the shift schedule.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class, 'template_id');
    }

    /**
     * Get the user who created the shift schedule.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the assignments for the shift schedule.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class, 'schedule_id');
    }
}
