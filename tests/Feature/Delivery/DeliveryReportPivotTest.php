<?php

use App\Delivery\Services\DeliveryReportImportService;
use App\Delivery\Services\DeliveryReportPivotService;
use App\Excel\Services\ExcelImportService;
use App\Models\DeliveryImportBatch;
use App\Models\DeliveryReportRow;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds column mapping safely with header aliases and missing columns', function (): void {
    /** @var DeliveryReportImportService $service */
    $service = new DeliveryReportImportService(app(ExcelImportService::class));

    $excelHeaders = [
        'Satınalma belgesi',
        'Kalem',
        'Firma Adı', // alias for FİRMA
        'İrsaliye No',
    ];

    $expectedHeaders = [
        'Satınalma belgesi', // 0
        'Kalem',             // 1
        'FİRMA',             // 2 (we want this mapped via alias)
        'İrsaliye No',       // 3
        'Tarih',             // 4 (will be missing -> -1)
    ];

    $aliases = [
        'FİRMA' => ['Firma_Adı', 'Firma Adı', 'FIRMA', 'firma_adı', 'Firma_adı'],
    ];

    $mapping = $service->buildColumnMapping($excelHeaders, $expectedHeaders, $aliases);

    expect($mapping)->toHaveCount(5);
    // "Satınalma belgesi" ve "Kalem" doğrudan eşleşmeli.
    expect($mapping[0])->toBe(0);
    expect($mapping[1])->toBe(1);
    // "FİRMA" alias "Firma Adı" sütununa map edilmeli.
    expect($mapping[2])->toBe(2);
    // "İrsaliye No" doğru index'e map edilmeli.
    expect($mapping[3])->toBe(3);
    // "Tarih" Excel başlıklarında yok; -1 olarak işaretlenmeli.
    expect($mapping[4])->toBe(-1);

    // Mapping -1 olsa bile normalizeRowToExpectedColumns güvenle çalışmalı.
    $excelRow = [
        0 => 'PO-001',
        1 => '10',
        2 => 'Firma X',
        3 => 'IRS-01',
    ];

    $dateExpectedIndices = [4];
    $timeExpectedIndices = [];
    $numericExpectedIndices = [1];

    $normalized = $service->normalizeRowToExpectedColumns(
        $excelRow,
        $mapping,
        count($expectedHeaders),
        $dateExpectedIndices,
        $timeExpectedIndices,
        $numericExpectedIndices
    );

    expect($normalized)->toHaveCount(5);
    expect($normalized[0])->toBe('PO-001');
    expect($normalized[1])->toBe('10');
    expect($normalized[2])->toBe('Firma X');
    expect($normalized[3])->toBe('IRS-01');
    // Eksik tarih kolonu boş string olarak normalize edilmeli.
    expect($normalized[4])->toBe('');
});

it('returns empty pivot and invoice lines for unsupported report types', function (): void {
    $batch = DeliveryImportBatch::factory()->create([
        'report_type' => 'unknown_type',
    ]);

    /** @var DeliveryReportPivotService $pivotService */
    $pivotService = app(DeliveryReportPivotService::class);

    expect($pivotService->buildPivot($batch))->toBe([]);
    expect($pivotService->buildInvoiceLines($batch))->toBe([]);
});

it('builds pivot and grouped invoice lines for dokme_cimento report type', function (): void {
    $batch = DeliveryImportBatch::factory()->create([
        'report_type' => 'dokme_cimento',
    ]);

    // dokme_cimento için config indeksleri:
    // 2 => Tarih, 5 => Malzeme, 7 => Miktar, 10 => Plaka, 11 => Firma, 12 => İrsaliye No
    $rowData1 = array_fill(0, 13, '');
    $rowData1[2] = '15.02.2026';
    $rowData1[5] = 'MAT-001';
    $rowData1[6] = 'Malzeme 1';
    $rowData1[7] = '10.5';
    $rowData1[10] = '34ABC123';
    $rowData1[11] = 'Firma A';
    $rowData1[12] = 'IRS-001';

    $rowData2 = $rowData1;
    $rowData2[7] = '5';

    DeliveryReportRow::query()->create([
        'delivery_import_batch_id' => $batch->id,
        'row_index' => 2,
        'row_data' => $rowData1,
    ]);

    DeliveryReportRow::query()->create([
        'delivery_import_batch_id' => $batch->id,
        'row_index' => 3,
        'row_data' => $rowData2,
    ]);

    /** @var DeliveryReportPivotService $pivotService */
    $pivotService = app(DeliveryReportPivotService::class);

    $pivot = $pivotService->buildPivot($batch);
    expect($pivot)->not()->toBeEmpty();

    $firstRow = $pivot[0];
    expect($firstRow['Tarih'])->toBe('15.02.2026');
    expect($firstRow['Firma'])->toBe('Firma A');
    expect($firstRow['Malzeme'])->toBe('MAT-001');
    expect($firstRow['İrsaliye No'])->toBe('IRS-001');
    expect($firstRow['Plaka'])->toBe('34ABC123');

    // Metrik etiketleri config'ten geliyor: 7 => 'Miktar', 'rows' => 'Satır sayısı'
    expect($firstRow['Miktar'])->toBe(15.5);
    expect($firstRow['Satır sayısı'])->toBe(2);

    $invoiceLines = $pivotService->buildInvoiceLines($batch, true);
    expect($invoiceLines)->toHaveCount(1);

    $line = $invoiceLines[0];
    expect($line['irsaliye_no'])->toBe('IRS-001');
    expect($line['malzeme_kodu'])->toBe('MAT-001');
    // Gruplanmış miktar (10.5 + 5) = 15.5
    expect($line['miktar'])->toBe(15.5);
});

it('returns empty pivot and invoice lines when batch has no report rows', function (): void {
    $batch = DeliveryImportBatch::factory()->create([
        'report_type' => 'dokme_cimento',
    ]);
    // reportRows ilişkisi boş; hiç DeliveryReportRow eklenmedi.

    $pivotService = app(DeliveryReportPivotService::class);

    expect($pivotService->buildPivot($batch))->toBe([]);
    expect($pivotService->buildInvoiceLines($batch))->toBe([]);
});

it('buildInvoiceLines without grouping returns one line per row', function (): void {
    $batch = DeliveryImportBatch::factory()->create([
        'report_type' => 'dokme_cimento',
    ]);

    $rowData1 = array_fill(0, 13, '');
    $rowData1[2] = '15.02.2026';
    $rowData1[5] = 'MAT-001';
    $rowData1[7] = '10';
    $rowData1[10] = '34ABC123';
    $rowData1[11] = 'Firma A';
    $rowData1[12] = 'IRS-001';

    $rowData2 = $rowData1;
    $rowData2[7] = '5';

    DeliveryReportRow::query()->create([
        'delivery_import_batch_id' => $batch->id,
        'row_index' => 1,
        'row_data' => $rowData1,
    ]);
    DeliveryReportRow::query()->create([
        'delivery_import_batch_id' => $batch->id,
        'row_index' => 2,
        'row_data' => $rowData2,
    ]);

    $pivotService = app(DeliveryReportPivotService::class);

    $grouped = $pivotService->buildInvoiceLines($batch, true);
    $ungrouped = $pivotService->buildInvoiceLines($batch, false);

    expect($grouped)->toHaveCount(1);
    expect($ungrouped)->toHaveCount(2);
});

it('handles row_data with missing or empty metric index gracefully', function (): void {
    $batch = DeliveryImportBatch::factory()->create([
        'report_type' => 'dokme_cimento',
    ]);

    $rowData = array_fill(0, 13, '');
    $rowData[2] = '16.02.2026';
    $rowData[5] = 'MAT-002';
    $rowData[6] = 'Malzeme 2';
    $rowData[7] = ''; // Miktar boş
    $rowData[10] = '06XYZ789';
    $rowData[11] = 'Firma B';
    $rowData[12] = 'IRS-002';

    DeliveryReportRow::query()->create([
        'delivery_import_batch_id' => $batch->id,
        'row_index' => 1,
        'row_data' => $rowData,
    ]);

    $pivotService = app(DeliveryReportPivotService::class);

    $pivot = $pivotService->buildPivot($batch);
    expect($pivot)->toHaveCount(1);
    expect($pivot[0]['Miktar'])->toBe(0.0);
    expect($pivot[0]['Satır sayısı'])->toBe(1);
});
