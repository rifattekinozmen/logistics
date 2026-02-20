<?php

namespace App\Delivery\Controllers\Web;

use App\Core\Services\ExportService;
use App\Delivery\Jobs\ProcessDeliveryImportJob;
use App\Delivery\Services\DeliveryReportImportService;
use App\Delivery\Services\DeliveryReportPivotService;
use App\Http\Controllers\Controller;
use App\Models\DeliveryImportBatch;
use App\Models\DeliveryReportRow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DeliveryImportController extends Controller
{
    /**
     * Liste: Yüklenmiş teslimat import batch'leri (durum ve tarih filtresi).
     */
    public function index(Request $request): View
    {
        $query = DeliveryImportBatch::query()->with(['importer'])->latest();

        $status = $request->string('status')->trim()->toString();
        if ($status !== '' && in_array($status, ['pending', 'processing', 'completed', 'failed'], true)) {
            $query->where('status', $status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $batches = $query->withCount('reportRows')->paginate(20)->withQueryString();

        return view('admin.delivery-imports.index', compact('batches'));
    }

    /**
     * Yeni Excel yükleme formu.
     */
    public function create(): View
    {
        $reportTypes = config('delivery_report.report_types', []);

        return view('admin.delivery-imports.create', compact('reportTypes'));
    }

    /**
     * Excel dosyasını al, batch kaydı oluştur ve işlenmek üzere kuyruğa hazırla.
     *
     * Not: Şimdilik sadece dosyayı ve batch kaydını oluşturuyoruz.
     * Gerçek Excel parse ve otomatik sipariş oluşturma akışı ayrı Job/Service ile eklenecek.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:20480',
            'report_type' => 'nullable|string|max:80',
        ]);

        $user = Auth::user();
        $company = $user->activeCompany();

        if (! $company) {
            return back()->withErrors([
                'file' => 'Aktif bir firma seçmeden teslimat numarası yükleyemezsiniz.',
            ]);
        }

        $file = $request->file('file');
        $path = $file->store('delivery-imports', 'private');

        $reportType = $request->string('report_type')->trim()->toString();
        $types = config('delivery_report.report_types', []);
        if ($reportType === '' || ! isset($types[$reportType])) {
            $reportType = array_key_first($types) ?: null;
        }

        $batch = DeliveryImportBatch::query()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'report_type' => $reportType,
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'status' => 'pending',
            'imported_by' => $user->id,
        ]);

        // Job'u dispatch et
        ProcessDeliveryImportJob::dispatch($batch, $company);

        return redirect()
            ->route('admin.delivery-imports.show', $batch)
            ->with('success', 'Dosya başarıyla yüklendi. İşleme alınmak üzere kuyruğa eklendi.');
    }

    /**
     * Tek bir batch ve normalize edilmiş rapor satırlarını başlıklara göre göster.
     * Batch hâlâ "pending" ise import burada senkron çalıştırılır (kuyruk worker yoksa içerik yine görünsün).
     */
    public function show(Request $request, DeliveryImportBatch $batch, DeliveryReportImportService $reportImportService): View
    {
        if (Schema::hasTable('delivery_report_rows') && $batch->status === 'pending') {
            try {
                $result = $reportImportService->importAndSaveReportRows($batch);
                $batch->update([
                    'total_rows' => $result['total_rows'],
                    'processed_rows' => $result['total_rows'],
                    'successful_rows' => $result['saved'],
                    'failed_rows' => count($result['errors']),
                    'import_errors' => $result['errors'],
                    'status' => 'completed',
                ]);
                $batch->refresh();
            } catch (Throwable $e) {
                $batch->update(['status' => 'failed']);
                report($e);
            }
        }

        $expectedHeaders = $reportImportService->getExpectedHeadersForBatch($batch);
        $search = $request->string('search')->trim()->toString();
        $sortCol = $request->integer('sort', -1);
        $direction = strtolower($request->string('direction', 'asc')->toString()) === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
        $errorRowIndexes = array_keys($batch->import_errors ?? []);

        if (! Schema::hasTable('delivery_report_rows')) {
            $reportRows = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, 1);
            $migrationMissing = true;
        } else {
            $query = $batch->reportRows();

            if ($search !== '') {
                $all = $query->orderBy('row_index')->get();
                $filtered = $all->filter(function (DeliveryReportRow $row) use ($search): bool {
                    $data = $row->row_data ?? [];
                    foreach ($data as $val) {
                        if (mb_stripos((string) $val, $search) !== false) {
                            return true;
                        }
                    }

                    return false;
                });

                if ($sortCol >= 0 && $sortCol < count($expectedHeaders)) {
                    $filtered = $filtered->sortBy(fn (DeliveryReportRow $r) => $r->row_data[$sortCol] ?? '', SORT_REGULAR, $direction === 'desc');
                } else {
                    $filtered = $filtered->sortBy('row_index', SORT_REGULAR, $direction === 'desc');
                }

                $page = max(1, $request->integer('page', 1));
                $slice = $filtered->values()->slice(($page - 1) * $perPage, $perPage);
                $reportRows = new \Illuminate\Pagination\LengthAwarePaginator(
                    $slice,
                    $filtered->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            } else {
                $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
                $sortInPhp = $driver === 'sqlsrv' && $sortCol >= 0 && $sortCol < count($expectedHeaders);

                if ($sortInPhp) {
                    $all = $query->orderBy('row_index')->get();
                    $sorted = $all->sortBy(
                        fn (DeliveryReportRow $r) => $r->row_data[$sortCol] ?? '',
                        SORT_REGULAR,
                        $direction === 'desc'
                    );
                    $page = max(1, $request->integer('page', 1));
                    $slice = $sorted->values()->slice(($page - 1) * $perPage, $perPage);
                    $reportRows = new \Illuminate\Pagination\LengthAwarePaginator(
                        $slice,
                        $sorted->count(),
                        $perPage,
                        $page,
                        ['path' => $request->url(), 'query' => $request->query()]
                    );
                } elseif ($sortCol >= 0 && $sortCol < count($expectedHeaders)) {
                    $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(row_data, '$[".$sortCol."]')) ".$direction);
                    $reportRows = $query->paginate($perPage)->withQueryString();
                } else {
                    $query->orderBy('row_index');
                    $reportRows = $query->paginate($perPage)->withQueryString();
                }
            }
            $migrationMissing = false;
        }

        $fileExists = $batch->file_path
            ? Storage::disk('private')->exists($batch->file_path)
            : false;

        $reportTypes = config('delivery_report.report_types', []);
        $reportTypeLabel = ($batch->report_type && isset($reportTypes[$batch->report_type]['label']))
            ? $reportTypes[$batch->report_type]['label']
            : null;
        $dateColumnIndices = ($batch->report_type && isset($reportTypes[$batch->report_type]['date_column_expected_indices']))
            ? $reportTypes[$batch->report_type]['date_column_expected_indices']
            : [];
        $timeColumnIndices = ($batch->report_type && isset($reportTypes[$batch->report_type]['time_column_expected_indices']))
            ? $reportTypes[$batch->report_type]['time_column_expected_indices']
            : [];
        $dateOnlyColumnIndices = ($batch->report_type && isset($reportTypes[$batch->report_type]['date_only_column_indices']))
            ? $reportTypes[$batch->report_type]['date_only_column_indices']
            : [];

        if (empty($dateColumnIndices) && ! empty($expectedHeaders)) {
            $tarihIndices = array_keys(array_filter($expectedHeaders, fn ($h): bool => trim((string) $h) === 'Tarih'));
            if ($tarihIndices !== []) {
                $dateColumnIndices = $tarihIndices;
                $dateOnlyColumnIndices = array_merge($dateOnlyColumnIndices, $tarihIndices);
            }
        }

        return view('admin.delivery-imports.show', [
            'batch' => $batch,
            'reportRows' => $reportRows,
            'expectedHeaders' => $expectedHeaders,
            'fileExists' => $fileExists,
            'migrationMissing' => $migrationMissing,
            'reportTypeLabel' => $reportTypeLabel,
            'errorRowIndexes' => $errorRowIndexes,
            'perPage' => $perPage,
            'dateColumnIndices' => $dateColumnIndices,
            'timeColumnIndices' => $timeColumnIndices,
            'dateOnlyColumnIndices' => $dateOnlyColumnIndices,
        ]);
    }

    /**
     * Rapor satırlarını Excel veya CSV olarak indir.
     */
    public function export(Request $request, DeliveryImportBatch $batch, DeliveryReportImportService $reportImportService): StreamedResponse|\Illuminate\Http\Response
    {
        $format = $request->string('format', 'xlsx')->toString();
        if (! in_array($format, ['xlsx', 'csv'], true)) {
            $format = 'xlsx';
        }

        $expectedHeaders = $reportImportService->getExpectedHeadersForBatch($batch);
        $query = $batch->reportRows()->orderBy('row_index');
        $rows = $query->get();

        $baseName = pathinfo($batch->file_name, PATHINFO_FILENAME).'_rapor_'.now()->format('Y-m-d_His');

        $formattedRows = $rows->map(function (DeliveryReportRow $row) use ($reportImportService, $batch): array {
            $rowData = $row->row_data ?? [];
            $formatted = $reportImportService->formatRowDataForDisplay($batch, $rowData);

            return array_merge([$row->row_index], $formatted);
        })->all();

        if ($format === 'csv') {
            return app(ExportService::class)->csv(
                array_merge(['#'], $expectedHeaders),
                $formattedRows,
                $baseName.'.csv'
            );
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(array_merge([array_merge(['#'], $expectedHeaders)], $formattedRows), null, 'A1');

        $writer = new XlsxWriter($spreadsheet);
        $filename = $baseName.'.xlsx';

        return response()->streamDownload(function () use ($writer): void {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.str_replace('"', '\\"', $filename).'"',
        ]);
    }

    /**
     * Veri Analiz Raporu: Tarih x Malzeme özeti (tarih aralığına göre X günlük başlık).
     */
    public function veriAnalizRaporu(DeliveryImportBatch $batch, DeliveryReportPivotService $pivotService): View|RedirectResponse
    {
        $pivot = $pivotService->buildMaterialPivot($batch);
        if ($pivot['materials'] === [] && $pivot['rows'] === []) {
            return redirect()->route('admin.delivery-imports.show', $batch)
                ->with('info', 'Bu rapor için malzeme pivot verisi yok veya rapor tipi desteklenmiyor.');
        }

        $reportTypes = config('delivery_report.report_types', []);
        $reportTypeLabel = ($batch->report_type && isset($reportTypes[$batch->report_type]['label']))
            ? $reportTypes[$batch->report_type]['label']
            : null;

        $rows = $pivot['rows'] ?? [];
        $dayCount = count($rows);
        $dateRangeText = '';
        if ($dayCount > 0) {
            $first = $rows[0]['tarih'] ?? '';
            $last = $rows[array_key_last($rows)]['tarih'] ?? '';
            $dateRangeText = $first === $last ? $first : $first."\u{2013}".$last;
        }

        return view('admin.delivery-imports.veri-analiz-raporu', [
            'batch' => $batch,
            'pivot' => $pivot,
            'reportTypeLabel' => $reportTypeLabel,
            'dayCount' => $dayCount,
            'dateRangeText' => $dateRangeText,
        ]);
    }

    /**
     * Günlük Klinker override değerlerini kaydet.
     * Kullanıcı kantar sistemindeki günlük Klinker miktarlarını girerek SAP-kantar farkını düzeltir.
     */
    public function updateKlinkerOverrides(Request $request, DeliveryImportBatch $batch): RedirectResponse
    {
        $valid = $request->validate([
            'overrides' => 'nullable|array',
            'overrides.*' => 'nullable|numeric|min:0',
        ]);

        $overrides = [];
        foreach ($valid['overrides'] ?? [] as $date => $val) {
            if ($val !== null && $val !== '' && (float) $val > 0) {
                $overrides[$date] = (float) $val;
            }
        }

        $batch->update(['klinker_daily_overrides' => $overrides ?: null]);

        return redirect()->route('admin.delivery-imports.veri-analiz-raporu', $batch)
            ->with('success', 'Günlük Klinker değerleri güncellendi.');
    }

    /**
     * Petrokok rota tercihini güncelle (Ekinciler / İsdemir).
     */
    public function updatePetrokokRoute(Request $request, DeliveryImportBatch $batch): RedirectResponse
    {
        $valid = $request->validate([
            'petrokok_route_preference' => 'required|string|in:ekinciler,isdemir',
        ]);
        $batch->update(['petrokok_route_preference' => $valid['petrokok_route_preference']]);

        return redirect()->route('admin.delivery-imports.veri-analiz-raporu', $batch)
            ->with('success', 'Petrokok rota tercihi güncellendi.');
    }

    /**
     * Fatura işlem durumunu güncelle (Fatura Beklemede / Fatura Oluşturuldu / Gönderildi).
     */
    public function updateInvoiceStatus(Request $request, DeliveryImportBatch $batch): RedirectResponse
    {
        $valid = $request->validate([
            'invoice_status' => 'required|string|in:pending,created,sent',
        ]);
        $batch->update(['invoice_status' => $valid['invoice_status']]);

        $back = $request->input('back', 'veri-analiz-raporu');
        if ($back === 'show') {
            return redirect()->route('admin.delivery-imports.show', $batch)
                ->with('success', 'Fatura durumu güncellendi.');
        }

        return redirect()->route('admin.delivery-imports.veri-analiz-raporu', $batch)
            ->with('success', 'Fatura durumu güncellendi.');
    }

    /**
     * Batch'i tekrar işle (report rows silinir, status pending yapılır; show'da yeniden import çalışır).
     */
    public function reprocess(DeliveryImportBatch $batch): RedirectResponse
    {
        $batch->reportRows()->delete();
        $batch->update([
            'status' => 'pending',
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'import_errors' => null,
        ]);

        return redirect()
            ->route('admin.delivery-imports.show', $batch)
            ->with('success', 'Rapor tekrar işlenecek. Sayfa yenilendiğinde import çalışacaktır.');
    }

    /**
     * Beklenen başlıklarla boş Excel şablonu indir. ?type= ile rapor tipi seçilebilir.
     */
    public function downloadTemplate(Request $request): StreamedResponse
    {
        $type = $request->string('type')->trim()->toString();
        $types = config('delivery_report.report_types', []);
        if ($type !== '' && isset($types[$type]['headers'])) {
            $expectedHeaders = $types[$type]['headers'];
        } else {
            $expectedHeaders = config('delivery_report.expected_headers', []);
        }
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$expectedHeaders], null, 'A1');

        $writer = new XlsxWriter($spreadsheet);
        $filename = 'teslimat_raporu_sablon_'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($writer): void {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.str_replace('"', '\\"', $filename).'"',
        ]);
    }

    /**
     * Yüklenen orijinal dosyayı indir.
     */
    public function downloadOriginal(DeliveryImportBatch $batch): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        if (! $batch->file_path || ! Storage::disk('private')->exists($batch->file_path)) {
            return redirect()->route('admin.delivery-imports.show', $batch)
                ->with('error', 'Dosya bulunamadı.');
        }

        return Storage::disk('private')->download(
            $batch->file_path,
            $batch->file_name
        );
    }

    /**
     * Batch'i sil (rapor satırları cascade silinir, orijinal dosya storage'dan kaldırılır).
     */
    public function destroy(DeliveryImportBatch $batch): RedirectResponse
    {
        if ($batch->file_path && Storage::disk('private')->exists($batch->file_path)) {
            Storage::disk('private')->delete($batch->file_path);
        }
        $batch->delete();

        return redirect()->route('admin.delivery-imports.index')
            ->with('success', 'Teslimat raporu silindi.');
    }
}
