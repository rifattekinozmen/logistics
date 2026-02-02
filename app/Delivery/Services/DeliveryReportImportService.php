<?php

namespace App\Delivery\Services;

use App\Excel\Services\ExcelImportService;
use App\Models\DeliveryImportBatch;
use App\Models\DeliveryReportRow;
use Illuminate\Support\Facades\Storage;

class DeliveryReportImportService
{
    public function __construct(
        protected ExcelImportService $excelImportService
    ) {}

    /**
     * Excel dosyasını beklenen başlıklara göre normalize edip delivery_report_rows olarak kaydeder.
     *
     * @return array{total_rows: int, saved: int, errors: array<int, string>}
     */
    public function importAndSaveReportRows(DeliveryImportBatch $batch): array
    {
        $path = Storage::disk('private')->path($batch->file_path);

        if (! file_exists($path)) {
            throw new \Exception("Dosya bulunamadı: {$batch->file_path}");
        }

        $expectedHeaders = $this->getExpectedHeadersForBatch($batch);
        $result = $this->excelImportService->readFileWithHeaders($batch->file_path, 'private');

        $excelHeaders = $result['headers'];
        $excelRows = $result['rows'];

        $aliases = $this->getHeaderAliasesForBatch($batch);
        $mapping = $this->buildColumnMapping($excelHeaders, $expectedHeaders, $aliases);
        $saved = 0;
        $errors = [];

        foreach ($excelRows as $index => $excelRow) {
            try {
                $rowData = $this->normalizeRowToExpectedColumns($excelRow, $mapping, count($expectedHeaders));
                DeliveryReportRow::query()->create([
                    'delivery_import_batch_id' => $batch->id,
                    'row_index' => $index + 2,
                    'row_data' => $rowData,
                ]);
                $saved++;
            } catch (\Throwable $e) {
                $errors[$index + 2] = $e->getMessage();
            }
        }

        return [
            'total_rows' => count($excelRows),
            'saved' => $saved,
            'errors' => $errors,
        ];
    }

    /**
     * Excel başlıklarını beklenen başlıklara eşler: expected_index => excel_column_index.
     * Tekrarlayan başlıklar soldan sağa sırayla eşleşir. Alias varsa önce ana başlık, sonra alias'lar denenir.
     *
     * @param  array<int, string>  $excelHeaders
     * @param  array<int, string>  $expectedHeaders
     * @param  array<string, array<int, string>>  $aliases  Başlık adı => Excel'de kabul edilen alternatif başlıklar
     * @return array<int, int>
     */
    public function buildColumnMapping(array $excelHeaders, array $expectedHeaders, array $aliases = []): array
    {
        $normalize = fn (string $s): string => mb_strtolower(trim($s), 'UTF-8');

        $mapping = [];
        $usedExcelIndices = [];

        foreach ($expectedHeaders as $expectedIndex => $expectedLabel) {
            $labelsToTry = array_merge([$expectedLabel], $aliases[$expectedLabel] ?? []);

            for ($j = 0; $j < count($excelHeaders); $j++) {
                if (in_array($j, $usedExcelIndices, true)) {
                    continue;
                }
                $excelNorm = $normalize($excelHeaders[$j]);
                foreach ($labelsToTry as $label) {
                    if ($excelNorm === $normalize($label)) {
                        $mapping[$expectedIndex] = $j;
                        $usedExcelIndices[] = $j;
                        break 2;
                    }
                }
            }

            if (! isset($mapping[$expectedIndex])) {
                $mapping[$expectedIndex] = -1;
            }
        }

        return $mapping;
    }

    /**
     * Rapor tipine göre başlık alias'larını döndürür (Excel'de farklı yazılan başlıkların eşlenmesi için).
     *
     * @return array<string, array<int, string>>
     */
    protected function getHeaderAliasesForBatch(DeliveryImportBatch $batch): array
    {
        $types = config('delivery_report.report_types', []);
        if (! $batch->report_type || ! isset($types[$batch->report_type]['header_aliases'])) {
            return [];
        }

        return $types[$batch->report_type]['header_aliases'];
    }

    /**
     * Tek bir Excel satırını beklenen kolon sırasına göre dizi olarak döndürür.
     *
     * @param  array<int, string>  $excelRow
     * @param  array<int, int>  $mapping  expected_index => excel_column_index
     * @return array<int, string>
     */
    public function normalizeRowToExpectedColumns(array $excelRow, array $mapping, int $expectedCount): array
    {
        $rowData = [];

        for ($i = 0; $i < $expectedCount; $i++) {
            $excelCol = $mapping[$i] ?? -1;
            $value = ($excelCol >= 0 && isset($excelRow[$excelCol]))
                ? (string) $excelRow[$excelCol]
                : '';
            $rowData[] = $value;
        }

        return $rowData;
    }

    /**
     * Batch'in rapor tipine göre beklenen başlıkları döndürür.
     *
     * @return array<int, string>
     */
    public function getExpectedHeadersForBatch(DeliveryImportBatch $batch): array
    {
        $types = config('delivery_report.report_types', []);
        if ($batch->report_type && isset($types[$batch->report_type]['headers'])) {
            return $types[$batch->report_type]['headers'];
        }

        return config('delivery_report.expected_headers', []);
    }
}
