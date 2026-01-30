<?php

namespace App\Excel\Jobs;

use App\Excel\Services\ExcelImportService;
use App\Excel\Services\PeriodCalculationService;
use App\Excel\Services\AnalysisService;
use App\Excel\Services\BillingService;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Genel Excel işleme job'u.
 * 
 * Raw tablolara kayıt, normalize etme, periyot hesaplama, analiz ve faturalandırma datası üretme.
 */
class ProcessExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $filePath,
        protected string $disk = 'private',
        protected ?Company $company = null,
        protected string $sourceType = 'operations' // operations, materials, logistics
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        ExcelImportService $excelService,
        PeriodCalculationService $periodService,
        AnalysisService $analysisService,
        BillingService $billingService
    ): void {
        try {
            // 1. Excel dosyasını parse et
            $rows = $excelService->parseFile($this->filePath, $this->disk);
            
            if (empty($rows)) {
                Log::warning("Excel dosyası boş veya parse edilemedi", [
                    'file_path' => $this->filePath,
                ]);
                return;
            }

            // 2. Raw tablolara kaydet (şimdilik log olarak, ileride ayrı tablo oluşturulabilir)
            Log::info("Excel raw data kaydedildi", [
                'row_count' => count($rows),
                'source_type' => $this->sourceType,
            ]);

            // 3. Veriyi normalize et ve periyot hesapla
            $processedData = [];
            foreach ($rows as $index => $row) {
                try {
                    $normalized = $excelService->normalizeRow($row);
                    
                    // Periyot hesapla
                    $period = $periodService->autoDetectPeriod($normalized, 'date');
                    if ($period) {
                        $normalized['period_key'] = $period['period_key'];
                        $normalized['period_start'] = $period['start_date'];
                        $normalized['period_end'] = $period['end_date'];
                    }

                    $processedData[] = $normalized;
                } catch (\Exception $e) {
                    Log::warning("Satır normalize edilemedi", [
                        'row_index' => $index,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 4. Analiz yap
            $analyzed = $analysisService->analyzeByPeriod($processedData);
            $summary = $analysisService->getSummary($processedData);

            // 5. Faturalandırma datası üret
            if ($this->company) {
                $billingData = $billingService->generateBillingData($analyzed, $this->company);
                $erpFormat = $billingService->formatForERP($billingData);

                // ERP formatını logla (ileride ERP'ye gönderilecek)
                Log::info("Faturalandırma datası üretildi", [
                    'company_id' => $this->company->id,
                    'billing_items_count' => count($billingData),
                    'erp_format' => $erpFormat,
                ]);
            }

            Log::info("Excel işleme tamamlandı", [
                'file_path' => $this->filePath,
                'total_rows' => count($rows),
                'processed_rows' => count($processedData),
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error("Excel işleme hatası: {$e->getMessage()}", [
                'file_path' => $this->filePath,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
