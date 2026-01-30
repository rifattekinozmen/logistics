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
