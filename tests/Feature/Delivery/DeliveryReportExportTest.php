<?php

use App\Delivery\Services\DeliveryReportPivotService;
use App\Models\DeliveryImportBatch;
use App\Models\DeliveryReportRow;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    [$this->user, $this->company] = createAdminUser();
});

function createDokmeCimentoBatchWithRows(): DeliveryImportBatch
{
    $batch = DeliveryImportBatch::factory()->create([
        'report_type' => 'dokme_cimento',
        'file_name' => 'dokme_cimento_test.xlsx',
    ]);

    // dokme_cimento config indeksleri:
    // 2 => Tarih, 5 => Malzeme, 6 => Malzeme kısa metni, 7 => Miktar,
    // 10 => Plaka, 11 => Firma, 12 => İrsaliye No.
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

    // Pivot'un gerçekten üretilebildiğini garanti altına al.
    /** @var DeliveryReportPivotService $pivotService */
    $pivotService = app(DeliveryReportPivotService::class);
    expect($pivotService->buildPivot($batch))->not()->toBeEmpty();
    expect($pivotService->buildInvoiceLines($batch))->not()->toBeEmpty();

    return $batch;
}

it('exports pivot summary as CSV', function (): void {
    $batch = createDokmeCimentoBatchWithRows();

    $response = $this->actingAs($this->user)
        ->withSession(['active_company_id' => $this->company->id])
        ->get(route('admin.delivery-imports.pivot-export', $batch));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = method_exists($response, 'streamedContent') ? $response->streamedContent() : (string) $response->getContent();
    expect((string) $content)->toContain('Tarih');
    expect((string) $content)->toContain('Miktar');
    expect((string) $content)->toContain('Satır sayısı');
});

it('exports grouped invoice lines as CSV', function (): void {
    $batch = createDokmeCimentoBatchWithRows();

    $response = $this->actingAs($this->user)
        ->withSession(['active_company_id' => $this->company->id])
        ->get(route('admin.delivery-imports.invoice-lines-export', [$batch, 'group' => 1]));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = method_exists($response, 'streamedContent') ? $response->streamedContent() : (string) $response->getContent();
    // Başlık satırında insan okunur isimler beklenir.
    expect((string) $content)->toContain('Malzeme Kodu');
    expect((string) $content)->toContain('Irsaliye No');
    // Gruplanmış tek satırda miktar toplamı 15.5 olmalı (nokta veya virgül formatında).
    expect((string) $content)->toContain('15.5');
});
