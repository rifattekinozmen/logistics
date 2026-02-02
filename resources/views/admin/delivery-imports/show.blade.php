@extends('layouts.app')

@section('title', 'Teslimat Raporu Detayı - Logistics')

@section('content')
@if($migrationMissing ?? false)
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
        <span class="material-symbols-outlined">warning</span>
        <div>
            <strong>delivery_report_rows</strong> tablosu bulunamadı. Rapor satırlarını görebilmek için terminalde şu komutu çalıştırın:
            <code class="d-block mt-2 bg-white px-2 py-1 rounded">php artisan migrate</code>
        </div>
    </div>
@endif
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="min-w-0 flex-grow-1">
        <h2 class="h3 fw-bold text-dark mb-1">Teslimat Raporu Detayı</h2>
        @if($reportTypeLabel ?? null)
            <p class="text-secondary mb-0">
                <span class="badge bg-primary-200 text-primary rounded-pill px-2 py-1 small">{{ $reportTypeLabel }}</span>
            </p>
        @endif
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2 ms-auto flex-shrink-0">
        @if(!($migrationMissing ?? false) && $reportRows->total() > 0)
            <a href="{{ route('admin.delivery-imports.veri-analiz-raporu', $batch) }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:1rem">table_chart</span>
                Veri Analiz Raporu
            </a>
            <a href="{{ route('admin.delivery-imports.export', [$batch, 'format' => 'xlsx']) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:1rem">download</span>
                Excel
            </a>
            <a href="{{ route('admin.delivery-imports.export', [$batch, 'format' => 'csv']) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:1rem">download</span>
                CSV
            </a>
        @endif
        @if(in_array($batch->status, ['pending', 'failed']))
            <form action="{{ route('admin.delivery-imports.reprocess', $batch) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:1rem">refresh</span>
                    Tekrar İşle
                </button>
            </form>
        @endif
        <a href="{{ route('admin.delivery-imports.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Listeye Dön
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-3 d-flex align-items-start gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size:1.25rem" aria-hidden="true">info</span>
            <div class="min-w-0">
                <div class="small text-secondary mb-1">Durum</div>
                @php
                    $statusColors = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                    ];
                    $statusLabels = [
                        'pending' => 'Beklemede',
                        'processing' => 'İşleniyor',
                        'completed' => 'Tamamlandı',
                        'failed' => 'Hata',
                    ];
                    $color = $statusColors[$batch->status] ?? 'secondary';
                    $label = $statusLabels[$batch->status] ?? $batch->status;
                @endphp
                <span class="badge bg-{{ $color }}-200 text-{{ $color }} rounded-pill px-3 py-2 fw-semibold">
                    {{ $label }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-3 d-flex align-items-start gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size:1.25rem" aria-hidden="true">format_list_numbered</span>
            <div class="min-w-0">
                <div class="small text-secondary mb-1">Toplam Satır</div>
                <div class="fw-bold text-dark">{{ $batch->total_rows ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-3 d-flex align-items-start gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size:1.25rem" aria-hidden="true">check_circle</span>
            <div class="min-w-0">
                <div class="small text-secondary mb-1">Başarılı / Hatalı</div>
                <div class="fw-bold text-dark">
                    {{ $batch->successful_rows ?? 0 }} / {{ $batch->failed_rows ?? 0 }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-3 d-flex align-items-start gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-size:1.25rem" aria-hidden="true">person</span>
            <div class="min-w-0">
                <div class="small text-secondary mb-1">Yükleyen</div>
                <div class="fw-bold text-dark">
                    {{ $batch->importer?->name ?? '-' }}
                </div>
                <div class="small text-secondary">
                    {{ $batch->created_at?->format('d.m.Y H:i') ?? '-' }}
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($batch->import_errors))
    <div class="alert alert-danger mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined">error</span>
            <strong>Hatalı satırlar ({{ count($batch->import_errors) }})</strong>
        </div>
        <ul class="mb-0 small">
            @foreach(array_slice($batch->import_errors, 0, 20, true) as $rowIndex => $message)
                <li>Satır <strong>{{ $rowIndex }}</strong>: {{ Str::limit($message, 80) }}</li>
            @endforeach
            @if(count($batch->import_errors) > 20)
                <li class="text-secondary">… ve {{ count($batch->import_errors) - 20 }} satır daha.</li>
            @endif
        </ul>
    </div>
@endif

<h3 class="h5 fw-semibold text-dark mb-3">Rapor satırları</h3>
<form method="GET" action="{{ route('admin.delivery-imports.show', $batch) }}" class="mb-3 d-flex flex-wrap align-items-center gap-2">
    <input type="hidden" name="sort" value="{{ request('sort', '') }}">
    <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
    <div class="input-group grow" style="max-width: 320px;">
        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tabloda ara…" aria-label="Ara">
        <button type="submit" class="btn btn-outline-primary">Ara</button>
        @if(request('search'))
            <a href="{{ route('admin.delivery-imports.show', [$batch, 'per_page' => $perPage ?? 25]) }}" class="btn btn-outline-secondary">Temizle</a>
        @endif
    </div>
    <label class="d-flex align-items-center gap-1 small text-secondary mb-0">
        <span>Sayfa başına:</span>
        <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
            @foreach([25, 50, 100] as $n)
                <option value="{{ $n }}" {{ ($perPage ?? 25) == $n ? 'selected' : '' }}>{{ $n }}</option>
            @endforeach
        </select>
    </label>
</form>

@php
    $dateColumnIndices = $dateColumnIndices ?? [];
    $timeColumnIndices = $timeColumnIndices ?? [];
    $dateOnlyColumnIndices = $dateOnlyColumnIndices ?? [];
    $normalizeAnyDateToDmY = function ($value) {
        if ($value === null || $value === '') {
            return '';
        }
        $str = trim((string) $value);
        if ($str === '') {
            return '';
        }
        $datePart = preg_replace('/\s+.*$/', '', $str);
        if (! preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}$/', $datePart)) {
            return $str;
        }
        $sep = strpos($datePart, '/') !== false ? '/' : '-';
        $parts = array_map('intval', explode($sep, $datePart));
        if (count($parts) !== 3 || $parts[2] < 1900 || $parts[2] > 2100) {
            return $str;
        }
        $a = $parts[0];
        $b = $parts[1];
        $y = $parts[2];
        if ($a > 12) {
            $d = $a;
            $m = $b;
        } elseif ($b > 12) {
            $m = $a;
            $d = $b;
        } else {
            $m = $a;
            $d = $b;
        }
        if ($m < 1 || $m > 12 || $d < 1 || $d > 31) {
            return $str;
        }

        return sprintf('%02d.%02d.%04d', $d, $m, $y);
    };
    $formatDateForDisplay = function ($value, $colIndex) use ($dateColumnIndices, $timeColumnIndices, $dateOnlyColumnIndices) {
        $isTime = in_array($colIndex, $timeColumnIndices, true);
        $isDate = in_array($colIndex, $dateColumnIndices, true);
        $dateOnly = in_array($colIndex, $dateOnlyColumnIndices, true);
        if (! $isTime && ! $isDate) {
            return $value === null || $value === '' ? '' : (string) $value;
        }
        if ($value === null || $value === '') {
            return '';
        }
        if (is_numeric($value) && class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
            $num = (float) $value;
            $prev = \PhpOffice\PhpSpreadsheet\Shared\Date::getExcelCalendar();
            $lastDt = null;
            $tz = new \DateTimeZone('Europe/Istanbul');
            try {
                foreach ([\PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_WINDOWS_1900, \PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_MAC_1904] as $cal) {
                    \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar($cal);
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($num, $tz);
                    $lastDt = $dt;
                    $year = (int) $dt->format('Y');
                    if ($year >= 1990 && $year <= 2030) {
                        \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar($prev);
                        if ($isTime) {
                            return $dt->format('g:i:s A');
                        }
                        if ($dateOnly) {
                            return $dt->format('d.m.Y');
                        }
                        $hasTime = (int) $dt->format('His') !== 0;

                        return $hasTime ? $dt->format('d.m.Y g:i:s A') : $dt->format('d.m.Y');
                    }
                }
                \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar($prev);
                if ($lastDt !== null) {
                    if ($isTime) {
                        return $lastDt->format('g:i:s A');
                    }
                    if ($dateOnly) {
                        return $lastDt->format('d.m.Y');
                    }
                    $hasTime = (int) $lastDt->format('His') !== 0;

                    return $hasTime ? $lastDt->format('d.m.Y g:i:s A') : $lastDt->format('d.m.Y');
                }
            } catch (\Throwable $e) {
                \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar($prev);
            }
        }
        $str = trim((string) $value);
        $formats = ['j.n.Y H:i:s', 'j.n.Y H:i', 'j.n.Y g:i:s A', 'j.n.Y', 'd.m.Y', 'd.m.Y H:i', 'd.m.Y g:i:s A', 'Y-m-d', 'Y-m-d H:i:s', 'n/j/Y', 'm/d/Y', 'n-j-Y', 'm-d-Y'];
        foreach ($formats as $fmt) {
            try {
                $parsed = \Carbon\Carbon::createFromFormat($fmt, $str);
                if ($parsed !== false) {
                    if ($isTime) {
                        return $parsed->format('g:i:s A');
                    }
                    if ($dateOnly) {
                        return $parsed->format('d.m.Y');
                    }
                    $hasTime = $parsed->format('His') !== '000000';

                    return $hasTime ? $parsed->format('d.m.Y g:i:s A') : $parsed->format('d.m.Y');
                }
            } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                continue;
            } catch (\Throwable $e) {
                continue;
            }
        }
        try {
            $parsed = \Carbon\Carbon::parse($value);
            if ($isTime) {
                return $parsed->format('g:i:s A');
            }
            if ($dateOnly) {
                return $parsed->format('d.m.Y');
            }
            $hasTime = $parsed->format('His') !== '000000';

            return $hasTime ? $parsed->format('d.m.Y g:i:s A') : $parsed->format('d.m.Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };
@endphp
@if(!($migrationMissing ?? false) && $reportRows->total() > 0)
    @php
        $rowDetailsMap = [];
        foreach ($reportRows as $r) {
            $rowData = $r->row_data ?? [];
            $out = [];
            foreach ($rowData as $idx => $val) {
                if (in_array($idx, $dateColumnIndices, true) || in_array($idx, $timeColumnIndices, true)) {
                    $out[$idx] = $formatDateForDisplay($val, $idx);
                } else {
                    $v = $val;
                    if ($v !== '' && $v !== null && preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}/', trim((string) $v))) {
                        $v = $normalizeAnyDateToDmY($v);
                    }
                    $out[$idx] = $v;
                }
            }
            $rowDetailsMap[$r->row_index] = $out;
        }
    @endphp
    <script type="application/json" id="row-details-data">{!! str_replace('</script>', '<\/script>', json_encode($rowDetailsMap)) !!}</script>
@endif
<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-sm">
            <thead class="bg-primary-200">
                <tr>
                    @php
                        $currentSort = request('sort', -1);
                        $currentDir = request('direction', 'asc');
                        $url = fn($col) => route('admin.delivery-imports.show', [$batch, 'search' => request('search'), 'sort' => $col, 'direction' => ($currentSort == $col && $currentDir === 'asc') ? 'desc' : 'asc', 'per_page' => $perPage ?? 25]);
                    @endphp
                    <th class="border-0 small fw-semibold text-nowrap text-dark">
                        <a href="{{ $url(-1) }}" class="text-decoration-none text-dark" title="Sıra (sıralama: Excel satır no)">Sıra</a>
                        @if((int) $currentSort === -1)<span class="material-symbols-outlined small align-middle">{{ $currentDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@endif
                    </th>
                    @foreach($expectedHeaders as $colIndex => $header)
                        <th class="border-0 small fw-semibold text-dark" style="white-space: normal; min-width: 6rem;" @if(trim((string) $header) === 'Tarih') title="Tarih (gg.aa.yyyy)" @endif>
                            <a href="{{ $url($colIndex) }}" class="text-decoration-none text-dark" title="{{ $header }} — Sırala">{{ $header }}</a>
                            @if((int) $currentSort === $colIndex)<span class="material-symbols-outlined small align-middle">{{ $currentDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($reportRows as $reportRow)
                    @php
                        $isErrorRow = in_array($reportRow->row_index, $errorRowIndexes ?? [], true);
                        $errorMessage = $isErrorRow && !empty($batch->import_errors[$reportRow->row_index])
                            ? $batch->import_errors[$reportRow->row_index]
                            : null;
                    @endphp
                    <tr class="delivery-report-row {{ $isErrorRow ? 'table-danger' : '' }}"
                        style="cursor: pointer;"
                        role="button"
                        tabindex="0"
                        data-row-index="{{ $reportRow->row_index }}"
                        data-display-index="{{ ($reportRows->currentPage() - 1) * $reportRows->perPage() + $loop->iteration }}"
                        data-error-message="{{ $errorMessage ? e($errorMessage) : '' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#rowDetailModal"
                        title="{{ $isErrorRow ? 'Hatalı satır — Detay için tıklayın' : 'Satır detayı için tıklayın' }}">
                        <td class="align-middle small text-body" title="Excel satır: {{ $reportRow->row_index }}">{{ ($reportRows->currentPage() - 1) * $reportRows->perPage() + $loop->iteration }}</td>
                        @foreach($expectedHeaders as $colIndex => $header)
                            @php
                                $raw = $reportRow->row_data[$colIndex] ?? '';
                                if (in_array($colIndex, $dateColumnIndices, true) || in_array($colIndex, $timeColumnIndices, true)) {
                                    $display = $formatDateForDisplay($raw, $colIndex);
                                } else {
                                    $display = (string) $raw;
                                    if ($display !== '' && preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}/', $display)) {
                                        $display = $normalizeAnyDateToDmY($display);
                                    }
                                }
                            @endphp
                            <td class="align-middle small text-nowrap text-body" title="{{ $raw }}">
                                {{ Str::limit($display, 40) }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($expectedHeaders) + 1 }}" class="text-center py-5">
                            <p class="text-secondary mb-0">
                                Bu import için henüz normalize edilmiş satır yok. İşlem tamamlandığında burada listelenecektir.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reportRows->hasPages())
        <div class="p-4 border-top">
            {{ $reportRows->links() }}
        </div>
    @endif
</div>

@if(!($migrationMissing ?? false))
<div class="modal fade" id="rowDetailModal" tabindex="-1" aria-labelledby="rowDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rowDetailModalLabel">Satır detayı — Sıra <span id="rowDetailDisplayIndex">-</span><span id="rowDetailExcelInfo" class="text-secondary fw-normal small ms-1"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body text-body">
                <div id="rowDetailError" class="alert alert-danger d-none mb-3" role="alert"></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <tbody id="rowDetailBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('rowDetailModal');
    if (!modal) return;
    var headers = @json($expectedHeaders);
    var rowDetailsEl = document.getElementById('row-details-data');
    var rowDetailsStore = rowDetailsEl ? (function () { try { return JSON.parse(rowDetailsEl.textContent); } catch (_) { return {}; } }()) : {};
    modal.addEventListener('show.bs.modal', function (e) {
        var trigger = e.relatedTarget;
        if (!trigger || !trigger.dataset.rowIndex) return;
        var rowIndex = trigger.dataset.rowIndex;
        var displayIndex = trigger.dataset.displayIndex || rowIndex;
        var rowData = rowDetailsStore[rowIndex] || rowDetailsStore[String(rowIndex)] || [];
        if (!Array.isArray(rowData)) { rowData = Object.keys(rowData).length ? Object.values(rowData) : []; }
        var errorMsg = trigger.dataset.errorMessage || '';
        document.getElementById('rowDetailDisplayIndex').textContent = displayIndex;
        document.getElementById('rowDetailExcelInfo').textContent = '(Excel satır: ' + rowIndex + ')';
        var errEl = document.getElementById('rowDetailError');
        if (errorMsg) {
            errEl.textContent = errorMsg;
            errEl.classList.remove('d-none');
        } else {
            errEl.classList.add('d-none');
        }
        var tbody = document.getElementById('rowDetailBody');
        tbody.innerHTML = '';
        headers.forEach(function (header, i) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<th class="text-body small text-nowrap fw-semibold" style="width:30%">' + escapeHtml(header) + '</th><td class="small text-body">' + escapeHtml(String(rowData[i] ?? '')) + '</td>';
            tbody.appendChild(tr);
        });
    });
    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }
});
</script>
@endif
@endsection

