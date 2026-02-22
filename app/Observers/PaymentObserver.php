<?php

namespace App\Observers;

use App\Core\Services\CalendarService;
use App\Models\Payment;

class PaymentObserver
{
    public function __construct(
        protected CalendarService $calendarService
    ) {
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        if ($payment->due_date && $payment->due_date->isFuture() && $payment->status === 'pending') {
            $this->calendarService->createFromPayment($payment);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        if ($payment->isDirty('due_date') && $payment->due_date) {
            $existingEvent = $payment->calendarEvents()->first();
            
            if ($existingEvent) {
                $this->calendarService->updateEvent($existingEvent, [
                    'start_date' => $payment->due_date,
                    'priority' => $this->calendarService->calculatePriority($payment->due_date),
                    'status' => $payment->status === 'paid' ? 'completed' : 'pending',
                ]);
            } elseif ($payment->due_date->isFuture() && $payment->status === 'pending') {
                $this->calendarService->createFromPayment($payment);
            }
        }

        if ($payment->isDirty('status') && $payment->status === 'paid') {
            $payment->calendarEvents()->update(['status' => 'completed']);
        }
    }
}
