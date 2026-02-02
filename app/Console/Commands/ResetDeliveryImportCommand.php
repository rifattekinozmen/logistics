<?php

namespace App\Console\Commands;

use App\Models\DeliveryImportBatch;
use Illuminate\Console\Command;

class ResetDeliveryImportCommand extends Command
{
    protected $signature = 'delivery-import:reset {id : Batch ID (örn. 17)}';

    protected $description = 'Teslimat import batch\'ini sıfırlar: rapor satırlarını siler, durumu pending yapar.';

    public function handle(): int
    {
        $id = (int) $this->argument('id');

        $batch = DeliveryImportBatch::find($id);
        if (! $batch) {
            $this->error("Batch {$id} bulunamadı.");

            return Command::FAILURE;
        }

        $deleted = $batch->reportRows()->delete();
        $batch->update([
            'status' => 'pending',
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'import_errors' => null,
        ]);

        $this->info("Import {$id} sıfırlandı: {$deleted} rapor satırı silindi, durum: pending.");
        $this->line('Rapor detay sayfasını yenilediğinizde Excel tekrar işlenecek.');

        return Command::SUCCESS;
    }
}
