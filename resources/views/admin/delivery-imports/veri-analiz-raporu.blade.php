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
                            $parts = explode(' | ', $m['label'], 2);
                            $code = $parts[0] ?? $m['label'];
                            $text = $parts[1] ?? '';
                        @endphp
                        <th class="border-0 text-secondary fw-semibold align-middle" style="white-space: normal; min-width: 4rem; line-height: 1.3; background-color: #e7f1ff;" title="{{ $m['label'] }}">
                            <div class="d-flex flex-column gap-0 justify-content-center align-items-center">
                                <span class="small text-dark">{{ $code }}</span>
                                @if($text !== '')
                                    <span class="small text-secondary">{{ $text }}</span>
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
                    <td class="align-middle text-start fw-medium text-dark">{{ $kalem['material_short'] }}</td>
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
@endif
@endsection
