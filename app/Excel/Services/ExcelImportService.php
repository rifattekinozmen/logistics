<?php

namespace App\Excel\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelImportService
{
    /**
     * Excel dosyasını parse et ve array olarak döndür.
     *
     * Not: Şimdilik basit CSV parsing. İleride Maatwebsite/Laravel-Excel paketi eklenebilir.
     */
    public function parseFile(string $filePath, string $disk = 'private'): array
    {
        $fullPath = Storage::disk($disk)->path($filePath);

        if (! file_exists($fullPath)) {
            throw new Exception("Dosya bulunamadı: {$filePath}");
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => $this->parseCsv($fullPath),
            'xlsx', 'xls' => $this->parseExcel($fullPath),
            default => throw new Exception("Desteklenmeyen dosya formatı: {$extension}"),
        };
    }

    /**
     * CSV dosyasını parse et.
     */
    protected function parseCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new Exception("CSV dosyası açılamadı: {$filePath}");
        }

        // İlk satırı header olarak al
        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);

            return [];
        }

        // Header'ları normalize et (trim, lowercase)
        $headers = array_map(function ($header) {
            return trim(strtolower($header));
        }, $headers);

        $rowNumber = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($data) !== count($headers)) {
                Log::warning("Satır {$rowNumber}: Header sayısı ile veri sayısı eşleşmiyor", [
                    'headers_count' => count($headers),
                    'data_count' => count($data),
                ]);

                continue;
            }

            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Excel dosyasını parse et (xlsx/xls). PhpSpreadsheet gerektirir.
     */
    protected function parseExcel(string $filePath): array
    {
        $this->ensurePhpSpreadsheetInstalled();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows)) {
            return [];
        }

        $headers = array_map(fn ($v) => trim((string) $v), $rows[0]);
        $dataRows = array_slice($rows, 1);
        $result = [];

        foreach ($dataRows as $row) {
            $values = array_map(fn ($v) => trim((string) $v), array_pad($row, count($headers), ''));
            $result[] = array_combine($headers, $values);
        }

        return $result;
    }

    /**
     * Excel/CSV dosyasını başlık + satırlar olarak oku.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function readFileWithHeaders(string $filePath, string $disk = 'private'): array
    {
        $fullPath = Storage::disk($disk)->path($filePath);

        if (! file_exists($fullPath)) {
            throw new Exception("Dosya bulunamadı: {$filePath}");
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => $this->readCsvWithHeaders($fullPath),
            'xlsx', 'xls' => $this->readXlsxWithHeaders($fullPath),
            default => throw new Exception("Desteklenmeyen dosya formatı: {$extension}"),
        };
    }

    protected function readCsvWithHeaders(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("CSV dosyası açılamadı: {$filePath}");
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);

            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(fn ($h) => trim((string) $h), $headers);
        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = array_map(fn ($v) => trim((string) $v), array_pad($data, count($headers), ''));
        }

        fclose($handle);

        return ['headers' => $headers, 'rows' => $rows, 'excel_calendar' => null];
    }

    protected function readXlsxWithHeaders(string $filePath): array
    {
        $this->ensurePhpSpreadsheetInstalled();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows)) {
            return ['headers' => [], 'rows' => [], 'excel_calendar' => null];
        }

        $headers = array_map(fn ($v) => trim((string) $v), $rows[0]);
        $dataRows = array_slice($rows, 1);
        $out = [];

        foreach ($dataRows as $row) {
            $padded = array_pad($row, count($headers), '');
            $out[] = array_map(function ($v) {
                if ($v === null || $v === '') {
                    return '';
                }
                if (is_numeric($v)) {
                    return $v;
                }

                return trim((string) $v);
            }, $padded);
        }

        $excelCalendar = null;
        if (method_exists($spreadsheet, 'getExcelCalendar')) {
            $cal = $spreadsheet->getExcelCalendar();
            if ($cal === \PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_WINDOWS_1900 || $cal === \PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_MAC_1904) {
                $excelCalendar = $cal;
            }
        }

        return ['headers' => $headers, 'rows' => $out, 'excel_calendar' => $excelCalendar];
    }

    /**
     * xlsx/xls okumak için PhpSpreadsheet paketinin kurulu olması gerekir.
     */
    protected function ensurePhpSpreadsheetInstalled(): void
    {
        if (! class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new Exception(
                'Excel (.xlsx) dosyalarını okuyabilmek için PhpSpreadsheet paketi gereklidir. '.
                'Proje klasöründe (logistics) terminalde şu komutu çalıştırın: composer require phpoffice/phpspreadsheet'
            );
        }
    }

    /**
     * Veriyi normalize et ve doğrula.
     */
    public function normalizeRow(array $row, array $requiredFields = []): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[trim(strtolower($key))] = trim($value);
        }

        // Gerekli alanları kontrol et
        foreach ($requiredFields as $field) {
            if (empty($normalized[$field])) {
                throw new Exception("Gerekli alan eksik: {$field}");
            }
        }

        return $normalized;
    }
}
