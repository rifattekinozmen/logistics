<?php

namespace App\FuelPrice\Jobs;

use App\FuelPrice\Services\FuelPriceReportService;
use App\Mail\FuelPriceWeeklyReportMail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateWeeklyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        protected ?Carbon $startDate = null,
        protected ?Carbon $endDate = null
    ) {
        $this->onQueue('low');
        $this->startDate = $startDate ?? now()->subWeek()->startOfWeek();
        $this->endDate = $endDate ?? now()->subWeek()->endOfWeek();
    }

    public function handle(FuelPriceReportService $reportService): void
    {
        try {
            $filepath = $reportService->generateWeeklyReport($this->startDate, $this->endDate);
            $summary = $reportService->getWeeklySummary($this->startDate, $this->endDate);

            $adminEmail = config('mail.admin_email', 'admin@example.com');

            if ($adminEmail && $adminEmail !== 'admin@example.com') {
                Mail::to($adminEmail)
                    ->send(new FuelPriceWeeklyReportMail($filepath, $summary));
            } else {
                $adminUsers = User::whereHas('roles', function ($query) {
                    $query->where('name', 'admin');
                })->get();

                foreach ($adminUsers as $user) {
                    if ($user->email) {
                        Mail::to($user->email)
                            ->send(new FuelPriceWeeklyReportMail($filepath, $summary));
                    }
                }
            }

            Log::info('Weekly fuel price report generated', [
                'start_date' => $this->startDate->format('Y-m-d'),
                'end_date' => $this->endDate->format('Y-m-d'),
                'filepath' => $filepath,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate weekly fuel price report', [
                'error' => $e->getMessage(),
                'start_date' => $this->startDate->format('Y-m-d'),
                'end_date' => $this->endDate->format('Y-m-d'),
            ]);

            throw $e;
        }
    }
}
