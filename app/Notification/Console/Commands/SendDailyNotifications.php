<?php

namespace App\Notification\Console\Commands;

use App\Models\Document;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

/**
 * Günlük bildirim gönderim komutu.
 * 
 * Belge süre bildirimleri, ödeme hatırlatmaları vb. gönderir.
 */
class SendDailyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Günlük bildirimleri gönder (belge süre, ödeme hatırlatmaları)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Günlük bildirimler gönderiliyor...');

        $companies = Company::where('is_active', true)->get();

        foreach ($companies as $company) {
            $this->checkDocumentExpiry($company);
            $this->checkPaymentReminders($company);
        }

        $this->info('Günlük bildirimler tamamlandı.');

        return Command::SUCCESS;
    }

    /**
     * Belge süre kontrolü.
     */
    protected function checkDocumentExpiry(Company $company): void
    {
        $today = Carbon::today();
        
        // 30 gün önce uyarı
        $expiringIn30Days = Document::where('valid_until', $today->copy()->addDays(30))
            ->whereNull('deleted_at')
            ->get();

        // 15 gün önce uyarı
        $expiringIn15Days = Document::where('valid_until', $today->copy()->addDays(15))
            ->whereNull('deleted_at')
            ->get();

        // 7 gün önce uyarı
        $expiringIn7Days = Document::where('valid_until', $today->copy()->addDays(7))
            ->whereNull('deleted_at')
            ->get();

        // Bugün sona erenler
        $expiredToday = Document::whereDate('valid_until', $today)
            ->whereNull('deleted_at')
            ->get();

        // Süresi geçenler
        $expired = Document::where('valid_until', '<', $today)
            ->whereNull('deleted_at')
            ->get();

        // Bildirimleri oluştur
        foreach ([
            ['documents' => $expiringIn30Days, 'days' => 30, 'severity' => 'low'],
            ['documents' => $expiringIn15Days, 'days' => 15, 'severity' => 'medium'],
            ['documents' => $expiringIn7Days, 'days' => 7, 'severity' => 'high'],
            ['documents' => $expiredToday, 'days' => 0, 'severity' => 'high'],
            ['documents' => $expired, 'days' => -1, 'severity' => 'high'],
        ] as $group) {
            if ($group['documents']->isNotEmpty()) {
                $this->createDocumentExpiryNotification($company, $group['documents'], $group['days'], $group['severity']);
            }
        }
    }

    /**
     * Belge süre bildirimi oluştur.
     */
    protected function createDocumentExpiryNotification(Company $company, $documents, int $days, string $severity): void
    {
        $message = $days === -1 
            ? "{$documents->count()} adet belgenin süresi geçmiş durumda."
            : "{$documents->count()} adet belge {$days} gün içinde sona erecek.";

        Notification::create([
            'notification_type' => 'document_expiry',
            'channel' => 'dashboard',
            'title' => 'Belge Süre Uyarısı',
            'content' => $message,
            'status' => 'pending',
            'metadata' => [
                'document_count' => $documents->count(),
                'days' => $days,
                'severity' => $severity,
            ],
        ]);
    }

    /**
     * Ödeme hatırlatmaları kontrolü.
     */
    protected function checkPaymentReminders(Company $company): void
    {
        $today = Carbon::today();

        // 7 gün kala
        $dueIn7Days = Payment::where('status', 0)
            ->whereDate('due_date', $today->copy()->addDays(7))
            ->get();

        // 3 gün kala
        $dueIn3Days = Payment::where('status', 0)
            ->whereDate('due_date', $today->copy()->addDays(3))
            ->get();

        // Bugün vadesi gelenler
        $dueToday = Payment::where('status', 0)
            ->whereDate('due_date', $today)
            ->get();

        // Gecikenler
        $overdue = Payment::where('status', 0)
            ->where('due_date', '<', $today)
            ->get();

        foreach ([
            ['payments' => $dueIn7Days, 'days' => 7],
            ['payments' => $dueIn3Days, 'days' => 3],
            ['payments' => $dueToday, 'days' => 0],
            ['payments' => $overdue, 'days' => -1],
        ] as $group) {
            if ($group['payments']->isNotEmpty()) {
                $this->createPaymentReminderNotification($company, $group['payments'], $group['days']);
            }
        }
    }

    /**
     * Ödeme hatırlatma bildirimi oluştur.
     */
    protected function createPaymentReminderNotification(Company $company, $payments, int $days): void
    {
        $totalAmount = $payments->sum('amount');
        
        $message = $days === -1
            ? "Geciken ödemeler: " . number_format($totalAmount, 2) . " TL ({$payments->count()} adet)"
            : "{$days} gün içinde vadesi gelecek ödemeler: " . number_format($totalAmount, 2) . " TL ({$payments->count()} adet)";

        Notification::create([
            'notification_type' => 'payment_reminder',
            'channel' => 'dashboard',
            'title' => $days === -1 ? 'Geciken Ödemeler' : 'Ödeme Hatırlatması',
            'content' => $message,
            'status' => 'pending',
            'metadata' => [
                'payment_count' => $payments->count(),
                'total_amount' => $totalAmount,
                'days' => $days,
            ],
        ]);
    }
}
