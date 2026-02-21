<?php

namespace App\Core\Services;

use App\Models\CalendarEvent;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * Get events for a specific month.
     */
    public function getEventsForMonth(int $year, int $month): Collection
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return CalendarEvent::query()
            ->betweenDates($startDate, $endDate)
            ->with(['related', 'creator'])
            ->get()
            ->map(fn ($event) => $this->formatEventForCalendar($event));
    }

    /**
     * Get events for a specific date range.
     */
    public function getEventsBetweenDates(Carbon $startDate, Carbon $endDate): Collection
    {
        return CalendarEvent::query()
            ->betweenDates($startDate, $endDate)
            ->with(['related', 'creator'])
            ->orderBy('start_date')
            ->get()
            ->map(fn ($event) => $this->formatEventForCalendar($event));
    }

    /**
     * Create a calendar event.
     */
    public function createEvent(array $data): CalendarEvent
    {
        return CalendarEvent::create($data);
    }

    /**
     * Create event from a Document.
     */
    public function createFromDocument(Document $document): CalendarEvent
    {
        return $this->createEvent([
            'title' => $document->title.' - Süre Sonu',
            'description' => 'Belge süre sonu: '.$document->expiry_date?->format('d.m.Y'),
            'event_type' => 'document',
            'related_type' => Document::class,
            'related_id' => $document->id,
            'start_date' => $document->expiry_date,
            'is_all_day' => true,
            'priority' => $this->calculatePriority($document->expiry_date),
            'color' => $this->getColorByDate($document->expiry_date),
            'status' => 'pending',
        ]);
    }

    /**
     * Create event from a Payment.
     */
    public function createFromPayment(Payment $payment): CalendarEvent
    {
        return $this->createEvent([
            'title' => 'Ödeme: '.$payment->description,
            'description' => 'Tutar: '.number_format($payment->amount, 2).' TL',
            'event_type' => 'payment',
            'related_type' => Payment::class,
            'related_id' => $payment->id,
            'start_date' => $payment->due_date,
            'is_all_day' => true,
            'priority' => $this->calculatePriority($payment->due_date),
            'color' => '#EF4444',
            'status' => 'pending',
        ]);
    }

    /**
     * Create event from a Vehicle maintenance/inspection.
     */
    public function createFromVehicleMaintenance(Vehicle $vehicle, Carbon $maintenanceDate, string $type = 'maintenance'): CalendarEvent
    {
        return $this->createEvent([
            'title' => $vehicle->plate.' - '.($type === 'inspection' ? 'Muayene' : 'Bakım'),
            'description' => 'Araç: '.$vehicle->brand.' '.$vehicle->model,
            'event_type' => $type,
            'related_type' => Vehicle::class,
            'related_id' => $vehicle->id,
            'start_date' => $maintenanceDate,
            'is_all_day' => true,
            'priority' => $this->calculatePriority($maintenanceDate),
            'color' => $type === 'inspection' ? '#8B5CF6' : '#F59E0B',
            'status' => 'pending',
        ]);
    }

    /**
     * Update an event.
     */
    public function updateEvent(CalendarEvent $event, array $data): CalendarEvent
    {
        $event->update($data);

        return $event->fresh();
    }

    /**
     * Delete an event.
     */
    public function deleteEvent(CalendarEvent $event): bool
    {
        return $event->delete();
    }

    /**
     * Get upcoming events (dashboard widget).
     */
    public function getUpcomingEvents(int $days = 7): Collection
    {
        return CalendarEvent::query()
            ->upcoming($days)
            ->with(['related'])
            ->limit(10)
            ->get();
    }

    /**
     * Get overdue events.
     */
    public function getOverdueEvents(): Collection
    {
        return CalendarEvent::query()
            ->overdue()
            ->with(['related'])
            ->limit(10)
            ->get();
    }

    /**
     * Get events that need reminders.
     */
    public function getEventsNeedingReminders(): Collection
    {
        return CalendarEvent::query()
            ->where('reminder_sent', false)
            ->where('status', 'pending')
            ->where('start_date', '>=', now())
            ->where('start_date', '<=', now()->addDays(30))
            ->orderBy('start_date')
            ->get()
            ->filter(fn ($event) => $event->needsReminder());
    }

    /**
     * Calculate priority based on date.
     */
    protected function calculatePriority(?Carbon $date): string
    {
        if (! $date) {
            return 'medium';
        }

        $daysUntil = now()->diffInDays($date, false);

        if ($daysUntil < 0) {
            return 'high';
        }
        if ($daysUntil <= 7) {
            return 'high';
        }
        if ($daysUntil <= 30) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get color based on date urgency.
     */
    protected function getColorByDate(?Carbon $date): string
    {
        if (! $date) {
            return '#3B82F6';
        }

        $daysUntil = now()->diffInDays($date, false);

        if ($daysUntil < 0) {
            return '#DC2626';
        }
        if ($daysUntil <= 7) {
            return '#F59E0B';
        }
        if ($daysUntil <= 30) {
            return '#FBBF24';
        }

        return '#10B981';
    }

    /**
     * Format event for FullCalendar.js.
     */
    protected function formatEventForCalendar(CalendarEvent $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start_date->format('Y-m-d').($event->start_time ? 'T'.$event->start_time->format('H:i:s') : ''),
            'end' => $event->end_date?->format('Y-m-d').($event->end_time ? 'T'.$event->end_time->format('H:i:s') : ''),
            'allDay' => $event->is_all_day,
            'backgroundColor' => $event->color,
            'borderColor' => $event->color,
            'extendedProps' => [
                'event_type' => $event->event_type,
                'priority' => $event->priority,
                'status' => $event->status,
                'description' => $event->description,
                'related_type' => $event->related_type,
                'related_id' => $event->related_id,
            ],
        ];
    }
}
