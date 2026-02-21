<?php

namespace App\Models;

use App\Core\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'related_type',
        'related_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_all_day',
        'priority',
        'status',
        'color',
        'reminder_sent',
        'reminder_sent_at',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_all_day' => 'boolean',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (CalendarEvent $event) {
            if (! $event->company_id) {
                $event->company_id = session('active_company_id');
            }
            if (! $event->created_by) {
                $event->created_by = auth()->id();
            }
        });
    }

    /**
     * Get the related model (Document, Payment, Vehicle, etc.)
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the company that owns the event.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Filter by event type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get upcoming events.
     */
    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('start_date', '>=', now())
            ->where('start_date', '<=', now()->addDays($days))
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date');
    }

    /**
     * Scope: Get overdue events.
     */
    public function scopeOverdue($query)
    {
        return $query->where('start_date', '<', now())
            ->where('status', 'pending')
            ->orderBy('start_date', 'desc');
    }

    /**
     * Scope: Get events for a specific date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    /**
     * Check if the event needs a reminder.
     */
    public function needsReminder(): bool
    {
        if ($this->reminder_sent || $this->status !== 'pending') {
            return false;
        }

        $daysUntil = now()->diffInDays($this->start_date, false);

        return match ($this->priority) {
            'high' => $daysUntil <= 7,
            'medium' => $daysUntil <= 3,
            'low' => $daysUntil <= 1,
            default => false,
        };
    }

    /**
     * Mark reminder as sent.
     */
    public function markReminderSent(): void
    {
        $this->update([
            'reminder_sent' => true,
            'reminder_sent_at' => now(),
        ]);
    }

    /**
     * Get the color based on priority if not set.
     */
    public function getColorAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return match ($this->priority) {
            'high' => '#EF4444',
            'medium' => '#F59E0B',
            'low' => '#10B981',
            default => '#3B82F6',
        };
    }
}
