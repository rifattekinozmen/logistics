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
        $excelCalendar = $result['excel_calendar'] ?? null;
        $dateColumnExpectedIndices = $this->getDateColumnExpectedIndicesForBatch($batch);
        $timeColumnExpectedIndices = $this->getTimeColumnExpectedIndicesForBatch($batch);
        $numericColumnExpectedIndices = $this->getNumericColumnExpectedIndicesForBatch($batch);

        $aliases = $this->getHeaderAliasesForBatch($batch);
        $mapping = $this->buildColumnMapping($excelHeaders, $expectedHeaders, $aliases);
        $saved = 0;
        $errors = [];

        foreach ($excelRows as $index => $excelRow) {
            try {
                $rowData = $this->normalizeRowToExpectedColumns($excelRow, $mapping, count($expectedHeaders), $dateColumnExpectedIndices, $timeColumnExpectedIndices, $numericColumnExpectedIndices, $excelCalendar);
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
     * Rapor tipine göre tarih sütunu expected index'lerini döndürür (import'ta Excel seri → d.m.Y için).
     *
     * @return array<int, int>
     */
    protected function getDateColumnExpectedIndicesForBatch(DeliveryImportBatch $batch): array
    {
        $types = config('delivery_report.report_types', []);
        if (! $batch->report_type || ! isset($types[$batch->report_type]['date_column_expected_indices'])) {
            return [];
        }

        return $types[$batch->report_type]['date_column_expected_indices'];
    }

    /**
     * Rapor tipine göre saat sütunu expected index'lerini döndürür (import'ta Excel seri → g:i:s A için).
     *
     * @return array<int, int>
     */
    protected function getTimeColumnExpectedIndicesForBatch(DeliveryImportBatch $batch): array
    {
        $types = config('delivery_report.report_types', []);
        if (! $batch->report_type || ! isset($types[$batch->report_type]['time_column_expected_indices'])) {
            return [];
        }

        return $types[$batch->report_type]['time_column_expected_indices'];
    }

    /**
     * Rapor tipine göre sayısal kolon expected index'lerini döndürür (TR 1.234,56 → 1234.56 saklanır).
     *
     * @return array<int, int>
     */
    protected function getNumericColumnExpectedIndicesForBatch(DeliveryImportBatch $batch): array
    {
        $types = config('delivery_report.report_types', []);
        if (! $batch->report_type || ! isset($types[$batch->report_type]['numeric_column_expected_indices'])) {
            return [];
        }

        return $types[$batch->report_type]['numeric_column_expected_indices'];
    }

    /**
     * Tek bir Excel satırını beklenen kolon sırasına göre normalize eder.
     * Tarih → d.m.Y, Saat → g:i:s A, Sayı → nokta ondalık string, Diğer → trim.
     *
     * @param  array<int, int>  $timeColumnExpectedIndices  Saat (g:i:s A)
     * @param  array<int, int>  $numericColumnExpectedIndices  Sayısal (TR → nokta ondalık)
     * @return array<int, string>
     */
    public function normalizeRowToExpectedColumns(array $excelRow, array $mapping, int $expectedCount, array $dateColumnExpectedIndices = [], array $timeColumnExpectedIndices = [], array $numericColumnExpectedIndices = [], ?int $excelCalendar = null): array
    {
        $rowData = [];
        $calendar = $excelCalendar ?? \PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_WINDOWS_1900;

        for ($i = 0; $i < $expectedCount; $i++) {
            $excelCol = $mapping[$i] ?? -1;
            $raw = ($excelCol >= 0 && array_key_exists($excelCol, $excelRow)) ? $excelRow[$excelCol] : null;

            if (in_array($i, $timeColumnExpectedIndices, true) && $raw !== null && $raw !== '') {
                $value = $this->formatExcelTimeForStorage($raw, $calendar);
            } elseif (in_array($i, $dateColumnExpectedIndices, true) && $raw !== null && $raw !== '') {
                $value = $this->formatExcelDateForStorage($raw, $calendar);
            } elseif (in_array($i, $numericColumnExpectedIndices, true)) {
                $value = $this->normalizeNumericForStorage($raw);
            } else {
                $value = $this->normalizeTextForStorage($raw);
            }

            $rowData[] = $value;
        }

        return $rowData;
    }

    /**
     * Metin/boş değeri normalize eder: trim, boş/null → ''.
     */
    protected function normalizeTextForStorage(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }

        return trim((string) $raw);
    }

    /**
     * Sayısal değeri normalize eder: TR (1.234,56) → nokta ondalık (1234.56) string.
     */
    protected function normalizeNumericForStorage(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }
        if (is_numeric($raw)) {
            return (string) (is_float($raw) || is_int($raw) ? $raw : (float) $raw);
        }
        $s = trim((string) $raw);
        if ($s === '') {
            return '';
        }
        $tr = preg_replace('/\s+/', '', $s);
        if (preg_match('/^[\d.,\-]+$/', $tr)) {
            if (str_contains($tr, ',')) {
                $tr = str_replace('.', '', $tr);
                $tr = str_replace(',', '.', $tr);
            }

            return is_numeric($tr) ? (string) (float) $tr : $s;
        }

        return $s;
    }

    /**
     * Excel tarih/saat değerini takvime göre d.m.Y veya d.m.Y 9:02:54 AM (g:i:s A) string'e çevirir.
     */
    protected function formatExcelDateForStorage(mixed $value, int $excelCalendar): string
    {
        $numericValue = null;
        if (is_numeric($value)) {
            $numericValue = (float) $value;
        } elseif (is_string($value)) {
            $candidate = str_replace(',', '.', trim($value));
            if (is_numeric($candidate)) {
                $numericValue = (float) $candidate;
            }
        }

        if ($numericValue !== null && $numericValue >= 1 && $numericValue < 2958466 && class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
            $dt = $this->excelSerialToDateTime($numericValue, $excelCalendar);
            if ($dt !== null) {
                $hasTime = (int) $dt->format('His') !== 0;

                return $hasTime ? $dt->format('d.m.Y g:i:s A') : $dt->format('d.m.Y');
            }
        }

        return trim((string) $value);
    }

    /**
     * Excel saat değerini (seri veya string) 9:02:54 AM (g:i:s A) string'e çevirir.
     */
    protected function formatExcelTimeForStorage(mixed $value, int $excelCalendar): string
    {
        $numericValue = null;
        if (is_numeric($value)) {
            $numericValue = (float) $value;
        } elseif (is_string($value)) {
            $candidate = str_replace(',', '.', trim($value));
            if (is_numeric($candidate)) {
                $numericValue = (float) $candidate;
            }
        }

        if ($numericValue !== null && class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
            $dt = $this->excelSerialToDateTime($numericValue, $excelCalendar);
            if ($dt !== null) {
                return $dt->format('g:i:s A');
            }
        }

        return trim((string) $value);
    }

    /**
     * Excel seri numarasını DateTime'a çevirir; takvim yanlışsa 1904 dener.
     */
    private function excelSerialToDateTime(float $numericValue, int $excelCalendar): ?\DateTimeInterface
    {
        $prev = \PhpOffice\PhpSpreadsheet\Shared\Date::getExcelCalendar();
        \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar($excelCalendar);
        try {
            $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numericValue);
            $year = (int) $dt->format('Y');
            $nowYear = (int) date('Y');
            if ($year > $nowYear + 1 || $year < $nowYear - 2) {
                \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar(\PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_MAC_1904);
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numericValue);
            }

            return $dt;
        } catch (\Throwable) {
            return null;
        } finally {
            \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar($prev);
        }
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
