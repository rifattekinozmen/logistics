<?php

namespace App\Notification\Console\Commands;

use App\Mail\DocumentExpiryReminderMail;
use App\Models\Document;
use App\Models\Notification;
use App\Notification\Services\NotificationChannelDispatcher;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckDocumentExpiryCommand extends Command
{
    protected $signature = 'documents:check-expiry';

    protected $description = 'Check for expiring documents and send notifications';

    public function handle(): int
    {
        $this->info('Checking document expiry dates...');

        $today = Document::query()
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', now()->toDateString())
            ->get();

        $in7Days = Document::query()
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', now()->addDays(7)->toDateString())
            ->get();

        $in15Days = Document::query()
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', now()->addDays(15)->toDateString())
            ->get();

        $in30Days = Document::query()
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', now()->addDays(30)->toDateString())
            ->get();

        $notificationsSent = 0;

        $dispatcher = app(NotificationChannelDispatcher::class);

        if ($today->isNotEmpty()) {
            foreach ($today as $document) {
                $this->createNotification($document, 0);
            }
            $this->sendEmailNotification($today, 0);
            $dispatcher->sendForScenario('document_expiry', 'document_expiry', [
                'days_until' => 0,
                'count' => $today->count(),
                'summary' => $today->count().' belge bugün süresi doluyor.',
            ]);
            $notificationsSent += $today->count();
        }

        if ($in7Days->isNotEmpty()) {
            foreach ($in7Days as $document) {
                $this->createNotification($document, 7);
            }
            $this->sendEmailNotification($in7Days, 7);
            $dispatcher->sendForScenario('document_expiry', 'document_expiry', [
                'days_until' => 7,
                'count' => $in7Days->count(),
                'summary' => $in7Days->count().' belge 7 gün içinde süresi dolacak.',
            ]);
            $notificationsSent += $in7Days->count();
        }

        if ($in15Days->isNotEmpty()) {
            foreach ($in15Days as $document) {
                $this->createNotification($document, 15);
            }
            $this->sendEmailNotification($in15Days, 15);
            $dispatcher->sendForScenario('document_expiry', 'document_expiry', [
                'days_until' => 15,
                'count' => $in15Days->count(),
                'summary' => $in15Days->count().' belge 15 gün içinde süresi dolacak.',
            ]);
            $notificationsSent += $in15Days->count();
        }

        if ($in30Days->isNotEmpty()) {
            foreach ($in30Days as $document) {
                $this->createNotification($document, 30);
            }
            $this->sendEmailNotification($in30Days, 30);
            $dispatcher->sendForScenario('document_expiry', 'document_expiry', [
                'days_until' => 30,
                'count' => $in30Days->count(),
                'summary' => $in30Days->count().' belge 30 gün içinde süresi dolacak.',
            ]);
            $notificationsSent += $in30Days->count();
        }

        $this->info("Sent {$notificationsSent} document expiry notifications.");

        return Command::SUCCESS;
    }

    protected function createNotification(Document $document, int $daysUntil): void
    {
        $severity = match (true) {
            $daysUntil <= 0 => 'high',
            $daysUntil <= 7 => 'high',
            $daysUntil <= 15 => 'medium',
            default => 'low',
        };

        $message = $daysUntil <= 0
            ? "Belge süresi doldu: {$document->name}"
            : "Belge {$daysUntil} gün içinde sona erecek: {$document->name}";

        Notification::create([
            'user_id' => $document->uploaded_by,
            'type' => 'document_expiry',
            'title' => 'Belge Süre Uyarısı',
            'message' => $message,
            'severity' => $severity,
            'related_type' => Document::class,
            'related_id' => $document->id,
            'is_read' => false,
        ]);
    }

    protected function sendEmailNotification($documents, int $daysUntil): void
    {
        $adminEmail = config('mail.admin_email', 'admin@example.com');

        if ($adminEmail && $adminEmail !== 'admin@example.com') {
            try {
                Mail::to($adminEmail)
                    ->send(new DocumentExpiryReminderMail($documents, $daysUntil));

                $this->info("Email sent to {$adminEmail} for {$daysUntil} days reminder.");
            } catch (Exception $e) {
                $this->error("Failed to send email: {$e->getMessage()}");
            }
        }
    }
}
