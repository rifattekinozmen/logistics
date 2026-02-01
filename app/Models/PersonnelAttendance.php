<?php

namespace App\Models;

use App\Casts\AttendanceStatusCast;
use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonnelAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personnel_attendance';

    protected $fillable = [
        'employee_id',
        'personel_id',
        'attendance_date',
        'attendance_type',
        'check_in',
        'check_out',
        'total_hours',
        'overtime_hours',
        'leave_type',
        'report_type',
        'report_document',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'attendance_type' => AttendanceStatusCast::class,
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'total_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Puantaj grid'i için frontend anahtarını döndürür (full, half, izin, yillik, rapor, none).
     */
    public function getFrontendStatusKey(): string
    {
        $status = $this->attendance_type;

        return $status instanceof AttendanceStatus
            ? $status->frontendKey()
            : 'none';
    }

    /**
     * Get the employee that owns the attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the personel that owns the attendance record.
     */
    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class);
    }

    /**
     * Get the user who approved the attendance.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
