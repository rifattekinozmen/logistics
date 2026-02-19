<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Notification;
use Illuminate\Console\Command;

class SendDailyNotificationsCommand extends Command
{
    protected $signature = 'notifications:send-daily';

    protected $description = 'Günlük bildirimleri gönder (vadesi yaklaşan ödemeler vb.)';

    public function handle(): int
    {
        $companies = Company::query()
            ->where('is_active', true)
            ->with('users')
            ->get();

        foreach ($companies as $company) {
            $this->sendDailyNotificationsForCompany($company);
        }

        $this->info('Günlük bildirimler gönderildi.');

        return self::SUCCESS;
    }

    /**
     * Belirli bir şirket için günlük bildirimleri gönder.
     */
    private function sendDailyNotificationsForCompany(Company $company): void
    {
        foreach ($company->users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'notification_type' => 'daily_summary',
                'channel' => 'system',
                'title' => 'Günlük Özet',
                'content' => now()->format('d.m.Y').' tarihli günlük özet bildirimi.',
                'status' => 'sent',
                'sent_at' => now(),
                'is_read' => false,
            ]);
        }
    }
}
