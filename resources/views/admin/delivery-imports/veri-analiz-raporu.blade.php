@extends('layouts.app')

@section('title', ($dayCount ?? 0) . ' Günlük Veri Analiz Raporu - Logistics')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">{{ $dayCount ?? 0 }} Günlük Veri Analiz Raporu</h2>
        @if($reportTypeLabel ?? null)
            <p class="mb-1">
                <span class="badge bg-primary-200 text-primary rounded-pill px-2 py-1 small">{{ $reportTypeLabel }}</span>
            </p>
        @endif
        @if($dateRangeText ?? '')
            <p class="text-secondary mb-0">{{ $dateRangeText }}</p>
        @endif
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.delivery-imports.show', $batch) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Rapor Detayı
        </a>
        <a href="{{ route('admin.delivery-imports.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">list</span>
            Listeye Dön
        </a>
    </div>
</div>

<style>
    /* Sütun aralığı: geniş = 0.75rem, dar = 0.35rem */
    .veri-analiz-table { --pivot-px: 0.5rem; --pivot-py: 0.5rem; }
    .veri-analiz-table th,
    .veri-analiz-table td { padding: var(--pivot-py) var(--pivot-px) !important; text-align: center; vertical-align: middle; }
    .veri-analiz-table th { white-space: normal; }
    .veri-analiz-table td { min-width: 0; }
</style>
<div class="bg-white rounded-3xl shadow-sm border overflow-hidden w-100" style="max-width: 100%;">
    <div class="w-100" style="overflow: visible;">
        <table class="table table-bordered table-hover table-sm mb-0 w-100 text-center veri-analiz-table" style="table-layout: auto; font-size: 0.875rem;">
            <thead>
                <tr style="background-color: #e7f1ff;">
                    <th class="border-0 text-secondary fw-semibold" style="width: 5rem; white-space: normal; background-color: #e7f1ff;" title="Tarih (gg.aa.yyyy)">TARİH</th>
                    @foreach($pivot['materials'] as $m)
                        @php
                            // matKey format: "KOD | KISA METİN" veya "KOD | KISA METİN [ÜY TANIM]"
                            $labelRaw = $m['label'];
                            $firmaSuffix = '';
                            if (preg_match('/\[(.+)\]\s*$/', $labelRaw, $fMatch)) {
                                $firmaSuffix = $fMatch[1];
                                $labelRaw = trim(preg_replace('/\s*\[.+\]\s*$/', '', $labelRaw));
                            }
                            $parts = explode(' | ', $labelRaw, 2);
                            $code = $parts[0] ?? $labelRaw;
                            $text = $parts[1] ?? '';
                        @endphp
                        <th class="border-0 text-secondary fw-semibold align-middle" style="white-space: normal; min-width: 4rem; line-height: 1.3; background-color: #e7f1ff;" title="{{ $m['label'] }}">
                            <div class="d-flex flex-column gap-0 justify-content-center align-items-center">
                                <span class="small text-dark">{{ $code }}</span>
                                @if($text !== '')
                                    <span class="small text-secondary">{{ $text }}</span>
                                @endif
                                @if($firmaSuffix !== '')
                                    <span class="small fw-semibold" style="color: #6f42c1; font-size: 0.7rem;">{{ $firmaSuffix }}</span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    <th class="border-0 text-secondary fw-semibold align-middle" style="white-space: normal; min-width: 4rem; background-color: #f0f4f8;">TOPLAM</th>
                    <th class="border-0 text-secondary fw-semibold align-middle text-primary" style="white-space: normal; min-width: 5rem; background-color: #cfe2ff;">
                        <div class="d-flex flex-column gap-0 lh-sm justify-content-center align-items-center">
                            <span>BOŞ-DOLU TAŞINAN</span>
                            <span>GEÇERLİ MİKTAR</span>
                        </div>
                    </th>
                    <th class="border-0 text-secondary fw-semibold align-middle" style="background-color: #d1e7dd; color: #0f5132; white-space: normal; min-width: 5rem;">
                        <div class="d-flex flex-column gap-0 lh-sm justify-content-center align-items-center">
                            <span>DOLU-DOLU TAŞINAN</span>
                            <span>GEÇERLİ MİKTAR</span>
                        </div>
                    </th>
                    <th class="border-0 text-secondary fw-semibold align-middle" style="white-space: normal; min-width: 8rem; background-color: #f0f4f8;">
                        <div class="d-flex flex-column gap-0 lh-sm justify-content-center align-items-center">
                            <span>BOŞ-DOLU TAŞINAN</span>
                            <span>MALZEME KISA METNİ</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse(($pivot['rows'] ?? []) as $row)
                    <tr>
                        <td class="fw-semibold align-middle">{{ $row['tarih'] }}</td>
                        @foreach($pivot['materials'] as $m)
                            @php
                                $qty = $row['material_totals'][$m['key']] ?? 0;
                                $adet = $row['material_counts'][$m['key']] ?? 0;
                            @endphp
                            <td class="align-middle small lh-sm">{{ number_format($qty, 2, ',', '.') }} Ton / {{ $adet }} Adet</td>
                        @endforeach
                        @php
                            $rowTotalQty = $row['row_total'];
                            $rowTotalAdet = $row['row_total_count'] ?? 0;
                        @endphp
                        <td class="fw-semibold align-middle small lh-sm" style="background-color: #f0f4f8;">{{ number_format($rowTotalQty, 2, ',', '.') }} Ton / {{ $rowTotalAdet }} Adet</td>
                        <td class="fw-semibold text-primary align-middle" style="background-color: #cfe2ff;">{{ number_format($row['boş_dolu'], 2, ',', '.') }}</td>
                        <td class="fw-semibold align-middle" style="background-color: #d1e7dd; color: #0f5132;">{{ number_format($row['dolu_dolu'], 2, ',', '.') }}</td>
                        <td class="align-middle" style="white-space: normal; min-width: 8rem; background-color: #f8f9fa; overflow: visible;">{{ $row['malzeme_kisa_metni'] ?? '--' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($pivot['materials'] ?? []) + 5 }}" class="text-center py-5 text-secondary">
                            Bu rapor için pivot verisi yok. Tarih sütunundaki değerler d.m.Y formatında normalize edilir.
                        </td>
                    </tr>
                @endforelse
                @if(!empty($pivot['rows']))
                <tr class="fw-bold" style="background-color: #e7f1ff;">
                    <td class="align-middle" style="background-color: #cfe2ff;">Toplam</td>
                    @foreach($pivot['materials'] as $m)
                        @php
                            $totQty = $pivot['totals_row']['material_totals'][$m['key']] ?? 0;
                            $totAdet = $pivot['totals_row']['material_counts'][$m['key']] ?? 0;
                        @endphp
                        <td class="align-middle small lh-sm" style="background-color: #e7f1ff;">{{ number_format($totQty, 2, ',', '.') }} Ton / {{ $totAdet }} Adet</td>
                    @endforeach
                    @php
                        $grandTotalQty = $pivot['totals_row']['row_total'];
                        $grandTotalAdet = $pivot['totals_row']['row_total_count'] ?? 0;
                    @endphp
                    <td class="align-middle small lh-sm" style="background-color: #cfe2ff;">{{ number_format($grandTotalQty, 2, ',', '.') }} Ton / {{ $grandTotalAdet }} Adet</td>
                    <td class="text-primary align-middle fw-bold" style="background-color: #cfe2ff;">{{ number_format($pivot['totals_row']['boş_dolu'], 2, ',', '.') }}</td>
                    <td class="align-middle fw-bold" style="background-color: #d1e7dd; color: #0f5132;">{{ number_format($pivot['totals_row']['dolu_dolu'], 2, ',', '.') }}</td>
                    <td class="align-middle" style="background-color: #cfe2ff;">-</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Günlük Klinker Düzeltme Paneli --}}
@php
    $klinkerRows = array_filter($pivot['rows'] ?? [], function($r) {
        foreach (array_keys($r['material_totals'] ?? []) as $mk) {
            if (stripos($mk, 'KLINKER') !== false) return true;
        }
        return false;
    });
    $hasKlinker = count($klinkerRows) > 0;
    $currentOverrides = $batch->klinker_daily_overrides ?? [];
@endphp

@if($hasKlinker)
<div class="mt-4">
    <details class="border rounded-3 bg-white shadow-sm" {{ !empty($currentOverrides) ? 'open' : '' }}>
        <summary class="px-3 py-2 fw-semibold text-secondary d-flex align-items-center gap-2" style="cursor:pointer; list-style:none; user-select:none; font-size:0.85rem;">
            <span class="material-symbols-outlined" style="font-size:1.1rem; color:#6f42c1;">edit_note</span>
            Günlük Klinker Düzeltme
            @if(!empty($currentOverrides))
                <span class="badge bg-purple-100 text-purple ms-1" style="background:#f3e8ff; color:#6f42c1; font-size:0.7rem;">Aktif</span>
            @endif
            <span class="ms-auto text-secondary fw-normal" style="font-size:0.75rem;">Kantar sistemi ile SAP tarihleme farkını düzelt</span>
        </summary>
        <div class="px-3 pb-3 pt-1">
            <p class="text-secondary small mb-2">
                SAP'daki günlük Klinker miktarı ile kantar sistemi arasında fark varsa, her gün için kantar değerini girin.
                Boş bırakılan günler SAP değeriyle hesaplanır.
            </p>
            <form method="POST" action="{{ route('admin.delivery-imports.klinker-overrides.update', $batch) }}">
                @csrf
                @method('PATCH')
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-2" style="font-size:0.82rem; max-width:480px;">
                        <thead>
                            <tr style="background:#f3e8ff;">
                                <th class="text-secondary fw-semibold py-1 px-2">Tarih</th>
                                <th class="text-secondary fw-semibold py-1 px-2">SAP (Geçerli Miktar)</th>
                                <th class="text-secondary fw-semibold py-1 px-2">Kantar (Manuel)</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($pivot['rows'] ?? [] as $pRow)
                            @php
                                $dayKl = 0;
                                foreach ($pRow['material_totals'] ?? [] as $mk => $mQty) {
                                    if (stripos($mk, 'KLINKER') !== false) $dayKl += $mQty;
                                }
                                if ($dayKl <= 0) continue;
                                $dateKey = $pRow['tarih'];
                                $overrideVal = $currentOverrides[$dateKey] ?? '';
                            @endphp
                            <tr>
                                <td class="fw-semibold align-middle px-2 py-1">{{ $dateKey }}</td>
                                <td class="align-middle px-2 py-1 text-secondary">{{ number_format($dayKl, 2, ',', '.') }}</td>
                                <td class="align-middle px-2 py-1">
                                    <input
                                        type="number"
                                        name="overrides[{{ $dateKey }}]"
                                        value="{{ $overrideVal !== '' ? number_format((float)$overrideVal, 2, '.', '') : '' }}"
                                        step="0.01"
                                        min="0"
                                        placeholder="{{ number_format($dayKl, 2, '.', '') }}"
                                        class="form-control form-control-sm"
                                        style="width:110px; font-size:0.82rem;"
                                    >
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="submit" class="btn btn-sm btn-outline-purple d-inline-flex align-items-center gap-1" style="border-color:#6f42c1; color:#6f42c1; font-size:0.82rem;">
                        <span class="material-symbols-outlined" style="font-size:1rem;">save</span>
                        Kaydet &amp; Hesapla
                    </button>
                    @if(!empty($currentOverrides))
                    <a href="#" onclick="clearOverrides(event)" class="text-danger small">Düzeltmeleri Sıfırla</a>
                    <form id="clearOverridesForm" method="POST" action="{{ route('admin.delivery-imports.klinker-overrides.update', $batch) }}" class="d-none">
                        @csrf
                        @method('PATCH')
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </details>
</div>
<script>
function clearOverrides(e) {
    e.preventDefault();
    if (confirm('Tüm Klinker düzeltmelerini sıfırlamak istediğinize emin misiniz?')) {
        document.getElementById('clearOverridesForm').submit();
    }
}
</script>
@endif

@if(!empty($pivot['fatura_rota_gruplari'] ?? []))
<style>
    .fatura-table { border-collapse: separate; border-spacing: 0; }
    .fatura-table th { font-size: 0.75rem; letter-spacing: 0.03em; text-transform: uppercase; padding: 0.65rem 0.75rem !important; }
    .fatura-table td { padding: 0.6rem 0.75rem !important; font-size: 0.84rem; }
    .fatura-route-header td { padding: 0.55rem 0.75rem !important; }
    .fatura-tip-badge { display: inline-block; font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 4px; letter-spacing: 0.02em; }
    .fatura-tip-dd { background-color: #d1e7dd; color: #0a5c36; }
    .fatura-tip-bd { background-color: #cfe2ff; color: #084298; }
    .fatura-arrow { color: #6c757d; font-size: 0.8rem; }
    .fatura-miktar { font-variant-numeric: tabular-nums; font-weight: 600; }
    .fatura-status-card { background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%); }
</style>

<div class="mt-5">
    {{-- Başlık kartı --}}
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
        <div>
            <h3 class="h5 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.3rem; color: #4361ee;">receipt_long</span>
                Fatura Kalemleri
            </h3>
            <p class="text-secondary small mb-0">
                <span class="fw-semibold">Fatura Dönemi:</span> {{ $dateRangeText ?? '–' }}
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            {{-- Petrokok Rota Tercihi --}}
            <form method="POST" action="{{ route('admin.delivery-imports.petrokok-route.update', $batch) }}" class="d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                @csrf
                @method('PATCH')
                <label class="text-secondary fw-semibold text-nowrap d-flex align-items-center gap-1" for="petrokok_route_pref">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">swap_horiz</span>
                    Petrokok Rotası:
                </label>
                <select name="petrokok_route_preference" id="petrokok_route_pref" class="form-select form-select-sm" style="width: auto; min-width: 150px; font-size: 0.82rem;" onchange="this.form.submit();">
                    <option value="ekinciler" {{ ($batch->petrokok_route_preference ?? 'ekinciler') === 'ekinciler' ? 'selected' : '' }}>Ekinciler Tesisi</option>
                    <option value="isdemir" {{ ($batch->petrokok_route_preference ?? 'ekinciler') === 'isdemir' ? 'selected' : '' }}>İsdemir Tesisi</option>
                </select>
            </form>
            <span class="fw-bold" style="font-size: 1.15rem; color: #1a1a2e;">{{ number_format($pivot['fatura_toplam'] ?? 0, 2, ',', '.') }} <span class="fw-normal text-secondary" style="font-size: 0.85rem;">Ton</span></span>
            <button type="button" id="btnCopyFatura" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" style="font-size: 0.82rem;" title="Mail İçin Kopyala" onclick="copyFaturaToClipboard()">
                <span class="material-symbols-outlined" style="font-size: 1rem;">content_copy</span>
            </button>
        </div>
    </div>

    {{-- Tablo --}}
    <div class="bg-white rounded-3 shadow-sm border overflow-hidden">
        <table class="table table-sm mb-0 w-100 fatura-table">
            <thead>
                <tr style="background: linear-gradient(180deg, #e9efff 0%, #dfe7f6 100%); border-bottom: 2px solid #c5d0e6;">
                    <th class="text-secondary fw-semibold text-start" style="width: 11%;">Malzeme Kodu</th>
                    <th class="text-secondary fw-semibold text-start" style="width: 16%;">Malzeme Kısa Metni</th>
                    <th class="text-secondary fw-semibold text-center" style="width: 26%;">Nerden Nereye</th>
                    <th class="text-secondary fw-semibold text-center" style="width: 12%;">Taşıma Tipi</th>
                    <th class="text-secondary fw-semibold text-end" style="width: 14%;">Toplam Miktar</th>
                    <th class="text-secondary fw-semibold text-center" style="width: 8%;">Birim</th>
                    <th class="text-secondary fw-semibold text-end" style="width: 13%;">Birim Fiyat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pivot['fatura_rota_gruplari'] as $gIdx => $rotaGrup)
                {{-- Rota grup başlığı --}}
                <tr class="fatura-route-header" style="background-color: #f1f4f9; border-top: {{ $gIdx > 0 ? '2px solid #dee2e6' : 'none' }};">
                    <td colspan="7" class="fw-bold" style="color: #2b3a67; font-size: 0.82rem;">
                        <span class="material-symbols-outlined align-middle me-1" style="font-size: 1rem; color: #4361ee;">location_on</span>
                        {{ $rotaGrup['route_label'] }}
                        <span class="text-secondary fw-normal ms-2" style="font-size: 0.75rem;">
                            ({{ number_format($rotaGrup['route_toplam'], 2, ',', '.') }} Ton)
                        </span>
                    </td>
                </tr>
                @foreach($rotaGrup['kalemler'] as $kalem)
                <tr style="border-bottom: 1px solid #eef0f4;">
                    <td class="align-middle text-start">
                        <code class="text-dark" style="font-size: 0.8rem; background: #f0f2f5; padding: 0.15rem 0.4rem; border-radius: 3px;">{{ $kalem['material_code'] }}</code>
                    </td>
                    <td class="align-middle text-start fw-medium text-dark">
                        @php
                            $matShort = $kalem['material_short'];
                            $faturaFirma = '';
                            if (preg_match('/\[(.+)\]\s*$/', $matShort, $fm)) {
                                $faturaFirma = $fm[1];
                                $matShort = trim(preg_replace('/\s*\[.+\]\s*$/', '', $matShort));
                            }
                        @endphp
                        {{ $matShort }}
                        @if($faturaFirma !== '')
                            <br><span class="small" style="color: #6f42c1; font-size: 0.72rem;">{{ $faturaFirma }}</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        @php
                            $dirParts = explode('→', $kalem['nerden_nereye']);
                            $from = trim($dirParts[0] ?? '');
                            $to = trim($dirParts[1] ?? '');
                        @endphp
                        <span class="text-dark">{{ $from }}</span>
                        <span class="fatura-arrow mx-1">→</span>
                        <span class="text-dark">{{ $to }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="fatura-tip-badge {{ $kalem['tasima_tipi'] === 'Dolu-Dolu' ? 'fatura-tip-dd' : 'fatura-tip-bd' }}">
                            {{ $kalem['tasima_tipi'] }}
                        </span>
                    </td>
                    <td class="align-middle text-end fatura-miktar">{{ number_format($kalem['miktar'], 2, ',', '.') }}</td>
                    <td class="align-middle text-center text-secondary">Ton</td>
                    <td class="align-middle text-end text-secondary">–</td>
                </tr>
                @endforeach
                @endforeach
                {{-- Toplam satırı --}}
                <tr style="background: linear-gradient(180deg, #e0e8f5 0%, #dbe4f3 100%); border-top: 2px solid #b8c4db;">
                    <td colspan="4" class="fw-bold text-end" style="font-size: 0.85rem; color: #2b3a67; padding-right: 1rem !important;">GENEL TOPLAM</td>
                    <td class="fw-bold text-end" style="font-size: 0.95rem; color: #1a1a2e;">{{ number_format($pivot['fatura_toplam'] ?? 0, 2, ',', '.') }}</td>
                    <td class="fw-bold text-center" style="color: #2b3a67;">Ton</td>
                    <td class="text-end text-secondary">–</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Firma bazlı fatura tabloları (1. tabloyla aynı yapı) --}}
    @foreach(($pivot['firma_fatura_gruplari'] ?? []) as $fgIdx => $firmaGrup)
    <div class="mt-4">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-2">
            <h4 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.2rem; color: #6f42c1;">business</span>
                {{ $firmaGrup['label'] }}
            </h4>
            <span class="fw-bold" style="font-size: 1rem; color: #1a1a2e;">{{ number_format($firmaGrup['toplam'], 2, ',', '.') }} <span class="fw-normal text-secondary" style="font-size: 0.8rem;">Ton</span></span>
        </div>

        <div class="bg-white rounded-3 shadow-sm border overflow-hidden">
            <table class="table table-sm mb-0 w-100 fatura-table">
                <thead>
                    <tr style="background: linear-gradient(180deg, #f3eeff 0%, #ece4fa 100%); border-bottom: 2px solid #d0c4e6;">
                        <th class="text-secondary fw-semibold text-start" style="width: 11%;">Malzeme Kodu</th>
                        <th class="text-secondary fw-semibold text-start" style="width: 16%;">Malzeme Kısa Metni</th>
                        <th class="text-secondary fw-semibold text-center" style="width: 26%;">Nerden Nereye</th>
                        <th class="text-secondary fw-semibold text-center" style="width: 12%;">Taşıma Tipi</th>
                        <th class="text-secondary fw-semibold text-end" style="width: 14%;">Toplam Miktar</th>
                        <th class="text-secondary fw-semibold text-center" style="width: 8%;">Birim</th>
                        <th class="text-secondary fw-semibold text-end" style="width: 13%;">Birim Fiyat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($firmaGrup['rota_gruplari'] as $rgIdx => $rotaGrup)
                    {{-- Rota grup başlığı --}}
                    <tr class="fatura-route-header" style="background-color: #f8f5ff; border-top: {{ $rgIdx > 0 ? '2px solid #e0dce6' : 'none' }};">
                        <td colspan="7" class="fw-bold" style="color: #4a347a; font-size: 0.82rem;">
                            <span class="material-symbols-outlined align-middle me-1" style="font-size: 1rem; color: #6f42c1;">location_on</span>
                            {{ $rotaGrup['route_label'] }}
                            <span class="text-secondary fw-normal ms-2" style="font-size: 0.75rem;">
                                ({{ number_format($rotaGrup['route_toplam'], 2, ',', '.') }} Ton)
                            </span>
                        </td>
                    </tr>
                    @foreach($rotaGrup['kalemler'] as $kalem)
                    <tr style="border-bottom: 1px solid #eef0f4;">
                        <td class="align-middle text-start">
                            <code class="text-dark" style="font-size: 0.8rem; background: #f0f2f5; padding: 0.15rem 0.4rem; border-radius: 3px;">{{ $kalem['material_code'] }}</code>
                        </td>
                        <td class="align-middle text-start fw-medium text-dark">
                            @php
                                $matShort2 = $kalem['material_short'];
                                $faturaFirma2 = '';
                                if (preg_match('/\[(.+)\]\s*$/', $matShort2, $fm2)) {
                                    $faturaFirma2 = $fm2[1];
                                    $matShort2 = trim(preg_replace('/\s*\[.+\]\s*$/', '', $matShort2));
                                }
                            @endphp
                            {{ $matShort2 }}
                            @if($faturaFirma2 !== '')
                                <br><span class="small" style="color: #6f42c1; font-size: 0.72rem;">{{ $faturaFirma2 }}</span>
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            @php
                                $dirParts2 = explode('→', $kalem['nerden_nereye']);
                                $from2 = trim($dirParts2[0] ?? '');
                                $to2 = trim($dirParts2[1] ?? '');
                            @endphp
                            <span class="text-dark">{{ $from2 }}</span>
                            <span class="fatura-arrow mx-1">→</span>
                            <span class="text-dark">{{ $to2 }}</span>
                        </td>
                        <td class="align-middle text-center">
                            <span class="fatura-tip-badge {{ $kalem['tasima_tipi'] === 'Dolu-Dolu' ? 'fatura-tip-dd' : 'fatura-tip-bd' }}">
                                {{ $kalem['tasima_tipi'] }}
                            </span>
                        </td>
                        <td class="align-middle text-end fatura-miktar">{{ number_format($kalem['miktar'], 2, ',', '.') }}</td>
                        <td class="align-middle text-center text-secondary">Ton</td>
                        <td class="align-middle text-end text-secondary">–</td>
                    </tr>
                    @endforeach
                    @endforeach
                    {{-- Toplam satırı --}}
                    <tr style="background: linear-gradient(180deg, #ece4fa 0%, #e5dcf5 100%); border-top: 2px solid #c4b5db;">
                        <td colspan="4" class="fw-bold text-end" style="font-size: 0.85rem; color: #4a347a; padding-right: 1rem !important;">GENEL TOPLAM</td>
                        <td class="fw-bold text-end" style="font-size: 0.95rem; color: #1a1a2e;">{{ number_format($firmaGrup['toplam'], 2, ',', '.') }}</td>
                        <td class="fw-bold text-center" style="color: #4a347a;">Ton</td>
                        <td class="text-end text-secondary">–</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    {{-- Fatura durumu kartı --}}
    <div class="fatura-status-card rounded-3 border mt-3 p-3 d-flex flex-wrap align-items-center gap-3">
        <span class="d-flex align-items-center gap-1 text-secondary" style="font-size: 0.82rem;">
            <span class="material-symbols-outlined" style="font-size: 1.1rem;">assignment</span>
            Fatura İşlem Durumu
        </span>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.delivery-imports.invoice-status.update', $batch) }}" class="d-inline" onsubmit="return confirm('Fatura durumunu «Fatura Beklemede» olarak güncellemek istediğinize emin misiniz?');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="invoice_status" value="pending">
                <input type="hidden" name="back" value="veri-analiz-raporu">
                <button type="submit" class="btn btn-sm {{ ($batch->invoice_status ?? null) === 'pending' ? 'btn-warning text-dark shadow-sm' : 'btn-outline-warning' }}" style="font-size: 0.78rem;">
                    <span class="material-symbols-outlined align-middle me-1" style="font-size: 0.95rem;">schedule</span>Beklemede
                </button>
            </form>
            <form method="POST" action="{{ route('admin.delivery-imports.invoice-status.update', $batch) }}" class="d-inline" onsubmit="return confirm('Fatura durumunu «Fatura Oluşturuldu» olarak güncellemek istediğinize emin misiniz?');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="invoice_status" value="created">
                <input type="hidden" name="back" value="veri-analiz-raporu">
                <button type="submit" class="btn btn-sm {{ ($batch->invoice_status ?? null) === 'created' ? 'btn-info text-white shadow-sm' : 'btn-outline-info' }}" style="font-size: 0.78rem;">
                    <span class="material-symbols-outlined align-middle me-1" style="font-size: 0.95rem;">check_circle</span>Oluşturuldu
                </button>
            </form>
            <form method="POST" action="{{ route('admin.delivery-imports.invoice-status.update', $batch) }}" class="d-inline" onsubmit="return confirm('Fatura durumunu «Gönderildi» olarak güncellemek istediğinize emin misiniz?');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="invoice_status" value="sent">
                <input type="hidden" name="back" value="veri-analiz-raporu">
                <button type="submit" class="btn btn-sm {{ ($batch->invoice_status ?? null) === 'sent' ? 'btn-success shadow-sm' : 'btn-outline-success' }}" style="font-size: 0.78rem;">
                    <span class="material-symbols-outlined align-middle me-1" style="font-size: 0.95rem;">send</span>Gönderildi
                </button>
            </form>
        </div>
        @if($batch->invoice_status ?? null)
        <span class="badge rounded-pill px-3 py-1 ms-auto {{ match($batch->invoice_status) { 'pending' => 'bg-warning text-dark', 'created' => 'bg-info text-white', 'sent' => 'bg-success', default => 'bg-secondary' } }}" style="font-size: 0.75rem;">
            {{ match($batch->invoice_status) { 'pending' => 'Beklemede', 'created' => 'Oluşturuldu', 'sent' => 'Gönderildi', default => $batch->invoice_status } }}
        </span>
        @endif
    </div>
</div>

<script>
function copyFaturaToClipboard() {
    const btn = document.getElementById('btnCopyFatura');
    const dateRange = @json($dateRangeText ?? '');
    const rotaGruplari = @json($pivot['fatura_rota_gruplari'] ?? []);
    const genelToplam = @json($pivot['fatura_toplam'] ?? 0);

    function formatNumber(n) {
        return Number(n).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function cleanBracket(text) {
        const m = text.match(/^(.*?)\s*\[(.+)\]\s*$/);
        return m ? { name: m[1].trim(), firma: m[2].trim() } : { name: text, firma: '' };
    }

    /* HTML tablo (mail istemcileri için) */
    const cellStyle = 'padding:6px 10px;border:1px solid #d0d0d0;font-size:13px;font-family:Calibri,Arial,sans-serif;';
    const headerStyle = cellStyle + 'background:#e9efff;font-weight:600;color:#2b3a67;';
    const routeStyle = cellStyle + 'background:#f1f4f9;font-weight:700;color:#2b3a67;font-size:13px;';
    const totalStyle = cellStyle + 'background:#e0e8f5;font-weight:700;color:#1a1a2e;';
    const ddBadge = 'display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;background:#d1e7dd;color:#0a5c36;';
    const bdBadge = 'display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;background:#cfe2ff;color:#084298;';

    let html = '';
    html += '<p style="font-family:Calibri,Arial,sans-serif;font-size:14px;color:#333;margin-bottom:8px;">';
    html += '<strong>Fatura Kalemleri</strong>';
    if (dateRange) html += ' &mdash; ' + dateRange;
    html += '</p>';
    html += '<table style="border-collapse:collapse;width:100%;max-width:800px;font-family:Calibri,Arial,sans-serif;">';
    html += '<thead><tr>';
    html += '<th style="' + headerStyle + 'text-align:left;">Malzeme Kodu</th>';
    html += '<th style="' + headerStyle + 'text-align:left;">Malzeme Kısa Metni</th>';
    html += '<th style="' + headerStyle + 'text-align:center;">Nerden Nereye</th>';
    html += '<th style="' + headerStyle + 'text-align:center;">Taşıma Tipi</th>';
    html += '<th style="' + headerStyle + 'text-align:right;">Toplam Miktar</th>';
    html += '<th style="' + headerStyle + 'text-align:center;">Birim</th>';
    html += '</tr></thead><tbody>';

    /* Düz metin (plain text fallback) */
    let plain = 'Fatura Kalemleri';
    if (dateRange) plain += ' — ' + dateRange;
    plain += '\n' + '='.repeat(70) + '\n\n';

    rotaGruplari.forEach(function(grup) {
        html += '<tr><td colspan="6" style="' + routeStyle + '">';
        html += '📍 ' + grup.route_label;
        html += ' <span style="font-weight:400;color:#6c757d;font-size:12px;">(' + formatNumber(grup.route_toplam) + ' Ton)</span>';
        html += '</td></tr>';

        plain += '▸ ' + grup.route_label + ' (' + formatNumber(grup.route_toplam) + ' Ton)\n';
        plain += '-'.repeat(70) + '\n';

        grup.kalemler.forEach(function(k) {
            const parsed = cleanBracket(k.material_short);
            const matDisplay = parsed.firma ? parsed.name + ' (' + parsed.firma + ')' : parsed.name;
            const tipBadge = k.tasima_tipi === 'Dolu-Dolu' ? ddBadge : bdBadge;

            html += '<tr>';
            html += '<td style="' + cellStyle + 'text-align:left;">' + k.material_code + '</td>';
            html += '<td style="' + cellStyle + 'text-align:left;">' + matDisplay + '</td>';
            html += '<td style="' + cellStyle + 'text-align:center;">' + k.nerden_nereye + '</td>';
            html += '<td style="' + cellStyle + 'text-align:center;"><span style="' + tipBadge + '">' + k.tasima_tipi + '</span></td>';
            html += '<td style="' + cellStyle + 'text-align:right;font-weight:600;">' + formatNumber(k.miktar) + '</td>';
            html += '<td style="' + cellStyle + 'text-align:center;">Ton</td>';
            html += '</tr>';

            const tipLabel = k.tasima_tipi === 'Dolu-Dolu' ? 'D-D' : 'B-D';
            plain += '  ' + k.material_code.padEnd(12) + matDisplay.padEnd(30) + tipLabel.padEnd(6) + formatNumber(k.miktar).padStart(12) + ' Ton\n';
            plain += '  ' + ' '.repeat(12) + k.nerden_nereye + '\n';
        });

        plain += '\n';
    });

    html += '<tr>';
    html += '<td colspan="4" style="' + totalStyle + 'text-align:right;">GENEL TOPLAM</td>';
    html += '<td style="' + totalStyle + 'text-align:right;font-size:14px;">' + formatNumber(genelToplam) + '</td>';
    html += '<td style="' + totalStyle + 'text-align:center;">Ton</td>';
    html += '</tr>';
    html += '</tbody></table>';

    plain += '='.repeat(70) + '\n';
    plain += 'GENEL TOPLAM: ' + formatNumber(genelToplam) + ' Ton\n';

    /* Clipboard'a kopyala (HTML + plain text) */
    const blob = new Blob([html], { type: 'text/html' });
    const blobPlain = new Blob([plain], { type: 'text/plain' });

    navigator.clipboard.write([
        new ClipboardItem({
            'text/html': blob,
            'text/plain': blobPlain
        })
    ]).then(function() {
        const origText = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:1rem;">check</span> Kopyalandı!';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success', 'text-white');
        setTimeout(function() {
            btn.innerHTML = origText;
            btn.classList.remove('btn-success', 'text-white');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(function() {
        /* Fallback: sadece plain text */
        navigator.clipboard.writeText(plain).then(function() {
            const origText = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:1rem;">check</span> Kopyalandı!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success', 'text-white');
            setTimeout(function() {
                btn.innerHTML = origText;
                btn.classList.remove('btn-success', 'text-white');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        });
    });
}
</script>
@endif
@endsection
