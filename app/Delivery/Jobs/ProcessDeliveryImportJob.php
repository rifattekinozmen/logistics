<?php

namespace App\Delivery\Jobs;

use App\Delivery\Services\DeliveryReportImportService;
use App\Models\Company;
use App\Models\DeliveryImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDeliveryImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected DeliveryImportBatch $batch,
        protected Company $company
    ) {}

    public function handle(DeliveryReportImportService $reportImportService): void
    {
        $this->batch->refresh();
        if ($this->batch->status !== 'pending') {
            return;
        }

        try {
            $this->batch->update(['status' => 'processing']);

            $result = $reportImportService->importAndSaveReportRows($this->batch);

            $failed = count($result['errors']);
            $successful = $result['saved'];

            $this->batch->update([
                'total_rows' => $result['total_rows'],
                'processed_rows' => $result['total_rows'],
                'successful_rows' => $successful,
                'failed_rows' => $failed,
                'import_errors' => $result['errors'],
                'status' => 'completed',
            ]);

            if (! empty($result['errors'])) {
                Log::warning('Teslimat raporu import kısmi hata', [
                    'batch_id' => $this->batch->id,
                    'errors' => $result['errors'],
                ]);
            }
        } catch (Throwable $e) {
            $this->batch->update(['status' => 'failed']);

            Log::error("Teslimat raporu import hatası: {$e->getMessage()}", [
                'batch_id' => $this->batch->id,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
