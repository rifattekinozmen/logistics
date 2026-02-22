<?php

namespace App\Notification\Console\Commands;

use App\Mail\PaymentDueReminderMail;
use App\Models\Notification;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckPaymentDueCommand extends Command
{
    protected $signature = 'payments:check-due';

    protected $description = 'Check for due payments and send notifications';

    public function handle(): int
    {
        $this->info('Checking payment due dates...');

        $today = Payment::query()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('due_date')
            ->whereDate('due_date', now()->toDateString())
            ->get();

        $in3Days = Payment::query()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('due_date')
            ->whereDate('due_date', now()->addDays(3)->toDateString())
            ->get();

        $in7Days = Payment::query()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('due_date')
            ->whereDate('due_date', now()->addDays(7)->toDateString())
            ->get();

        $overduePayments = Payment::query()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->get();

        $notificationsSent = 0;

        if ($today->isNotEmpty()) {
            foreach ($today as $payment) {
                $this->createNotification($payment, 0);
            }
            $this->sendEmailNotification($today, 0, false);
            $notificationsSent += $today->count();
        }

        if ($in3Days->isNotEmpty()) {
            foreach ($in3Days as $payment) {
                $this->createNotification($payment, 3);
            }
            $this->sendEmailNotification($in3Days, 3, false);
            $notificationsSent += $in3Days->count();
        }

        if ($in7Days->isNotEmpty()) {
            foreach ($in7Days as $payment) {
                $this->createNotification($payment, 7);
            }
            $this->sendEmailNotification($in7Days, 7, false);
            $notificationsSent += $in7Days->count();
        }

        if ($overduePayments->isNotEmpty()) {
            foreach ($overduePayments as $payment) {
                $daysOverdue = now()->diffInDays($payment->due_date, false);
                $this->createOverdueNotification($payment, abs($daysOverdue));
            }
            $this->sendEmailNotification($overduePayments, 0, true);
            $notificationsSent += $overduePayments->count();
        }

        $this->info("Sent {$notificationsSent} payment due notifications.");

        return Command::SUCCESS;
    }

    protected function createNotification(Payment $payment, int $daysUntil): void
    {
        $severity = match (true) {
            $daysUntil <= 0 => 'high',
            $daysUntil <= 3 => 'high',
            $daysUntil <= 7 => 'medium',
            default => 'low',
        };

        $message = $daysUntil <= 0
            ? "Ödeme vadesi doldu: {$payment->description} - ".number_format($payment->amount, 2)." TL"
            : "Ödeme {$daysUntil} gün içinde yapılmalı: {$payment->description} - ".number_format($payment->amount, 2)." TL";

        Notification::create([
            'user_id' => $payment->created_by,
            'type' => 'payment_due',
            'title' => 'Ödeme Hatırlatma',
            'message' => $message,
            'severity' => $severity,
            'related_type' => Payment::class,
            'related_id' => $payment->id,
            'is_read' => false,
        ]);
    }

    protected function createOverdueNotification(Payment $payment, int $daysOverdue): void
    {
        Notification::create([
            'user_id' => $payment->created_by,
            'type' => 'payment_overdue',
            'title' => 'Gecikmiş Ödeme',
            'message' => "Ödeme {$daysOverdue} gün gecikmiş: {$payment->description} - ".number_format($payment->amount, 2).' TL',
            'severity' => 'high',
            'related_type' => Payment::class,
            'related_id' => $payment->id,
            'is_read' => false,
        ]);
    }

    protected function sendEmailNotification($payments, int $daysUntil, bool $isOverdue): void
    {
        $adminEmail = config('mail.admin_email', 'admin@example.com');

        if ($adminEmail && $adminEmail !== 'admin@example.com') {
            try {
                Mail::to($adminEmail)
                    ->send(new PaymentDueReminderMail($payments, $daysUntil, $isOverdue));

                $status = $isOverdue ? 'overdue' : "{$daysUntil} days";
                $this->info("Email sent to {$adminEmail} for {$status} reminder.");
            } catch (Exception $e) {
                $this->error("Failed to send email: {$e->getMessage()}");
            }
        }
    }
}
