<?php

namespace App\Excel\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Dosya bulunamadı: {$filePath}");
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        
        return match($extension) {
            'csv' => $this->parseCsv($fullPath),
            'xlsx', 'xls' => $this->parseExcel($fullPath),
            default => throw new \Exception("Desteklenmeyen dosya formatı: {$extension}"),
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
            throw new \Exception("CSV dosyası açılamadı: {$filePath}");
        }

        // İlk satırı header olarak al
        $headers = fgetcsv($handle);
        
        if ($headers === false) {
            fclose($handle);
            return [];
        }

        // Header'ları normalize et (trim, lowercase)
        $headers = array_map(function($header) {
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
     * Excel dosyasını parse et.
     * 
     * Not: Şimdilik basit implementasyon. İleride Maatwebsite/Laravel-Excel kullanılabilir.
     */
    protected function parseExcel(string $filePath): array
    {
        // Basit CSV gibi davran (gerçek Excel parsing için paket gerekli)
        // Şimdilik CSV formatında kaydedilmiş Excel dosyalarını destekle
        return $this->parseCsv($filePath);
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
                throw new \Exception("Gerekli alan eksik: {$field}");
            }
        }

        return $normalized;
    }
}
