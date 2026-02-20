<?php

namespace App\AI\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIDocumentService
{
    /**
     * Extract invoice data from an image or PDF using AI/OCR.
     *
     * @param  string  $filePath  Path to the invoice file
     * @return array<string, mixed> Extracted invoice data
     *
     * @throws Exception On extraction failure
     */
    public function extractInvoiceData(string $filePath): array
    {
        if (! Storage::exists($filePath)) {
            throw new Exception('File not found: '.$filePath);
        }

        $fileContents = Storage::get($filePath);
        $mimeType = Storage::mimeType($filePath);

        // For demo purposes, return mock data
        // In production, this would call an AI service like Azure Form Recognizer or AWS Textract
        if (config('ai.provider') === 'mock') {
            return $this->mockExtractInvoiceData($filePath);
        }

        try {
            $response = Http::timeout(60)
                ->attach('file', $fileContents, basename($filePath))
                ->post(config('ai.ocr_endpoint'), [
                    'document_type' => 'invoice',
                ]);

            if ($response->successful()) {
                return $this->parseInvoiceResponse($response->json());
            }

            throw new Exception('OCR service returned error: '.$response->status());
        } catch (Exception $e) {
            Log::error('Invoice data extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Classify a document type using AI.
     *
     * @param  string  $filePath  Path to the document file
     * @return string Document classification
     *
     * @throws Exception On classification failure
     */
    public function classifyDocument(string $filePath): string
    {
        if (! Storage::exists($filePath)) {
            throw new Exception('File not found: '.$filePath);
        }

        // For demo purposes, classify by extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match (strtolower($extension)) {
            'pdf' => 'invoice',
            'jpg', 'jpeg', 'png' => 'receipt',
            'xlsx', 'xls' => 'delivery_report',
            'docx', 'doc' => 'contract',
            default => 'unknown',
        };
    }

    /**
     * Validate document compliance (e.g., E-Invoice format validation).
     *
     * @param  \App\Models\Document  $document  Document model
     * @return array<string, mixed> Validation result
     */
    public function validateCompliance($document): array
    {
        $errors = [];
        $warnings = [];

        // Mock validation logic
        if (! isset($document->file_path)) {
            $errors[] = 'Missing file path';
        }

        if (! isset($document->document_type)) {
            $warnings[] = 'Document type not specified';
        }

        $isValid = empty($errors);
        $complianceScore = $isValid ? (empty($warnings) ? 100 : 85) : 0;

        return [
            'is_valid' => $isValid,
            'compliance_score' => $complianceScore,
            'errors' => $errors,
            'warnings' => $warnings,
            'recommendations' => $this->generateComplianceRecommendations($errors, $warnings),
        ];
    }

    /**
     * Mock invoice data extraction (for demo/testing).
     *
     * @param  string  $filePath  File path
     * @return array<string, mixed> Mock extracted data
     */
    protected function mockExtractInvoiceData(string $filePath): array
    {
        return [
            'invoice_number' => 'INV-'.rand(10000, 99999),
            'invoice_date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
            'vendor_name' => 'ABC Lojistik A.Ş.',
            'vendor_tax_number' => '1234567890',
            'total_amount' => rand(1000, 50000),
            'currency' => 'TRY',
            'line_items' => [
                [
                    'description' => 'Navlun Bedeli',
                    'quantity' => 1,
                    'unit_price' => rand(1000, 50000),
                ],
            ],
            'confidence' => 0.95,
        ];
    }

    /**
     * Parse OCR response into structured invoice data.
     *
     * @param  array<string, mixed>  $response  OCR API response
     * @return array<string, mixed> Parsed invoice data
     */
    protected function parseInvoiceResponse(array $response): array
    {
        return [
            'invoice_number' => $response['document']['invoice_number'] ?? null,
            'invoice_date' => $response['document']['date'] ?? null,
            'vendor_name' => $response['document']['vendor']['name'] ?? null,
            'vendor_tax_number' => $response['document']['vendor']['tax_id'] ?? null,
            'total_amount' => $response['document']['total'] ?? 0,
            'currency' => $response['document']['currency'] ?? 'TRY',
            'line_items' => $response['document']['items'] ?? [],
            'confidence' => $response['confidence'] ?? 0,
        ];
    }

    /**
     * Generate compliance recommendations.
     *
     * @param  array<int, string>  $errors  Validation errors
     * @param  array<int, string>  $warnings  Validation warnings
     * @return array<int, string> Recommendations
     */
    protected function generateComplianceRecommendations(array $errors, array $warnings): array
    {
        $recommendations = [];

        if (! empty($errors)) {
            $recommendations[] = 'Fix critical errors before proceeding';
        }

        if (! empty($warnings)) {
            $recommendations[] = 'Address warnings to improve compliance score';
        }

        if (empty($errors) && empty($warnings)) {
            $recommendations[] = 'Document is fully compliant';
        }

        return $recommendations;
    }
}
