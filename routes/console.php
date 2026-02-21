<?php

use App\AI\Jobs\RunAIAnalysisJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// AI Analiz Cronjob - Günlük 09:00'da çalışır
Schedule::job(new RunAIAnalysisJob)
    ->dailyAt('09:00')
    ->name('ai-analysis')
    ->withoutOverlapping();

// Günlük Bildirimler - Her gün 00:05'te çalışır
Schedule::command('notifications:send-daily')
    ->dailyAt('00:05')
    ->name('daily-notifications')
    ->withoutOverlapping();

// Belge Süre Kontrolleri - Her gün 08:00'de çalışır
Schedule::command('documents:check-expiry')
    ->dailyAt('08:00')
    ->name('document-expiry-check')
    ->withoutOverlapping();

// Ödeme Vade Kontrolleri - Her gün 08:30'da çalışır
Schedule::command('payments:check-due')
    ->dailyAt('08:30')
    ->name('payment-due-check')
    ->withoutOverlapping();

// Haftalık Motorin Fiyat Raporu - Her Pazar 20:00'da
Schedule::job(new \App\FuelPrice\Jobs\GenerateWeeklyReportJob)
    ->weeklyOn(0, '20:00')
    ->name('weekly-fuel-report')
    ->withoutOverlapping();
