<?php

namespace App\Console\Commands;

use App\Models\DeliveryImportBatch;
use App\Models\DeliveryReportRow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResetDeliveryImportIdsCommand extends Command
{
    protected $signature = 'delivery-import:reset-ids
                            {--force : Onay istemeden çalıştır}';

    protected $description = 'Tüm teslimat import kayıtlarını siler ve batch ID\'yi 1\'den başlatır. Orijinal dosyalar storage\'dan da silinir.';

    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Tüm teslimat raporları ve rapor satırları silinecek. Orijinal Excel dosyaları da kaldırılacak. Devam edilsin mi?')) {
                return Command::SUCCESS;
            }
        }

        $driver = DB::connection()->getDriverName();
        $this->line("Veritabanı sürücüsü: <comment>{$driver}</comment>");

        $batches = DeliveryImportBatch::all();
        $batchCount = $batches->count();
        $this->line("Silinecek batch sayısı: {$batchCount}");

        foreach ($batches as $batch) {
            if ($batch->file_path && Storage::disk('private')->exists($batch->file_path)) {
                Storage::disk('private')->delete($batch->file_path);
            }
        }

        $reportRowsDeleted = DeliveryReportRow::query()->delete();
        $this->line("delivery_report_rows silindi: {$reportRowsDeleted} satır");

        $numbersUpdated = DB::table('delivery_numbers')->whereNotNull('import_batch_id')->update(['import_batch_id' => null]);
        $this->line("delivery_numbers.import_batch_id null yapıldı: {$numbersUpdated} satır");

        $batchesDeleted = DeliveryImportBatch::query()->delete();
        $this->line("delivery_import_batches silindi: {$batchesDeleted} satır");

        $reseedRan = false;
        if ($driver === 'sqlsrv') {
            DB::statement('DBCC CHECKIDENT (delivery_import_batches, RESEED, 0)');
            $reseedRan = true;
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE delivery_import_batches AUTO_INCREMENT = 1');
            $reseedRan = true;
        } elseif ($driver === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('delivery_import_batches', 'id'), 0)");
            $reseedRan = true;
        } elseif ($driver === 'sqlite') {
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'delivery_import_batches'");
            $reseedRan = true;
        }

        if ($reseedRan) {
            $this->info('Identity/sequence 1\'den başlayacak şekilde sıfırlandı.');
        } else {
            $this->warn("Sürücü '{$driver}' için RESEED tanımlı değil. Bir sonraki ID otomatik artmaya devam edebilir.");
        }

        $this->info('Teslimat import kayıtları silindi. Bir sonraki yükleme ID 1 ile başlayacak.');

        return Command::SUCCESS;
    }
}
