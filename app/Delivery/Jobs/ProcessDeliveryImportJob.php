<?php

namespace App\Delivery\Jobs;

use App\Delivery\Services\LocationMatchingService;
use App\Delivery\Services\AutoOrderCreationService;
use App\Excel\Services\ExcelImportService;
use App\Models\DeliveryImportBatch;
use App\Models\DeliveryNumber;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessDeliveryImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected DeliveryImportBatch $batch,
        protected Company $company
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        ExcelImportService $excelService,
        LocationMatchingService $locationService,
        AutoOrderCreationService $orderService
    ): void {
        try {
            $this->batch->update(['status' => 'processing']);

            // Excel dosyasını parse et
            $rows = $excelService->parseFile($this->batch->file_path, 'private');
            
            if (empty($rows)) {
                $this->batch->update([
                    'status' => 'failed',
                    'error_message' => 'Dosya boş veya parse edilemedi.',
                ]);
                return;
            }

            $this->batch->update(['total_rows' => count($rows)]);

            $successful = 0;
            $failed = 0;

            DB::transaction(function () use ($rows, $excelService, $locationService, $orderService, &$successful, &$failed) {
                foreach ($rows as $index => $row) {
                    try {
                        // Gerekli alanları kontrol et
                        $normalized = $excelService->normalizeRow($row, [
                            'teslimat_no',
                            'musteri_adi',
                            'teslimat_adresi',
                        ]);

                        // Teslimat numarası oluştur
                        $deliveryNumber = DeliveryNumber::create([
                            'company_id' => $this->company->id,
                            'delivery_number' => $normalized['teslimat_no'] ?? $normalized['delivery_number'] ?? null,
                            'customer_name' => $normalized['musteri_adi'] ?? $normalized['customer_name'] ?? null,
                            'customer_phone' => $normalized['musteri_telefon'] ?? $normalized['customer_phone'] ?? null,
                            'delivery_address' => $normalized['teslimat_adresi'] ?? $normalized['delivery_address'] ?? null,
                            'import_batch_id' => $this->batch->id,
                            'row_number' => $index + 2, // +2 çünkü header var ve index 0'dan başlıyor
                            'status' => 'new',
                        ]);

                        // Lokasyon eşleştirmesi
                        if ($deliveryNumber->delivery_address) {
                            $location = $locationService->matchLocation($deliveryNumber->delivery_address);
                            if ($location) {
                                $deliveryNumber->update(['location_id' => $location->id]);
                            }
                        }

                        // Otomatik sipariş oluştur
                        $order = $orderService->createOrderFromDeliveryNumber($deliveryNumber, $this->company);
                        
                        if ($order) {
                            $deliveryNumber->update(['status' => 'order_created']);
                            $successful++;
                        } else {
                            $deliveryNumber->update(['status' => 'matched']); // Lokasyon eşleşti ama sipariş oluşturulamadı
                            $successful++;
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        Log::error("Teslimat import hatası (Satır {$index}): {$e->getMessage()}", [
                            'batch_id' => $this->batch->id,
                            'row' => $row,
                            'exception' => $e,
                        ]);
                    }

                    $this->batch->increment('processed_rows');
                }
            });

            $this->batch->update([
                'status' => 'completed',
                'successful_rows' => $successful,
                'failed_rows' => $failed,
            ]);
        } catch (\Exception $e) {
            $this->batch->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Teslimat import job hatası: {$e->getMessage()}", [
                'batch_id' => $this->batch->id,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
