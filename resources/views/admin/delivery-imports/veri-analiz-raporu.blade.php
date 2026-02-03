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

@if(!empty($pivot['fatura_kalemleri'] ?? []))
<div class="mt-5">
    <h3 class="h5 fw-bold text-dark mb-3">Fatura Kalemleri</h3>
    <p class="text-secondary small mb-2"><strong>Fatura Dönemi:</strong> {{ $dateRangeText ?? '–' }}</p>
    <div class="bg-white rounded-3xl shadow-sm border overflow-hidden w-100">
        <table class="table table-bordered table-sm mb-0 w-100 veri-analiz-table" style="font-size: 0.875rem;">
            <thead>
                <tr style="background-color: #e7f1ff;">
                    <th class="text-secondary fw-semibold">Malzeme Kodu / Lokasyon Nereden Nereye + Malzeme Kısa Metni</th>
                    <th class="text-secondary fw-semibold text-center">Taşıma Tipi</th>
                    <th class="text-secondary fw-semibold text-center">Toplam Miktar</th>
                    <th class="text-secondary fw-semibold text-center">Birim</th>
                    <th class="text-secondary fw-semibold text-center">Birim Fiyat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pivot['fatura_kalemleri'] as $kalem)
                <tr>
                    <td class="align-middle">{{ $kalem['material_label'] }}</td>
                    <td class="align-middle text-center">{{ $kalem['tasima_tipi'] }}</td>
                    <td class="align-middle text-center">{{ number_format($kalem['miktar'], 2, ',', '.') }}</td>
                    <td class="align-middle text-center">Ton</td>
                    <td class="align-middle text-center">–</td>
                </tr>
                @endforeach
                <tr class="fw-bold" style="background-color: #e7f1ff;">
                    <td class="align-middle" colspan="2">TOPLAM</td>
                    <td class="align-middle text-center">{{ number_format($pivot['fatura_toplam'] ?? 0, 2, ',', '.') }}</td>
                    <td class="align-middle text-center">Ton</td>
                    <td class="align-middle text-center">–</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
        <span class="text-secondary small me-2">Fatura işlem durumu:</span>
        <form method="POST" action="{{ route('admin.delivery-imports.invoice-status.update', $batch) }}" class="d-inline" onsubmit="return confirm('Fatura durumunu «Fatura Beklemede» olarak güncellemek istediğinize emin misiniz?');">
            @csrf
            @method('PATCH')
            <input type="hidden" name="invoice_status" value="pending">
            <input type="hidden" name="back" value="veri-analiz-raporu">
            <button type="submit" class="btn {{ ($batch->invoice_status ?? null) === 'pending' ? 'btn-warning' : 'btn-outline-warning' }} btn-sm">Fatura Beklemede</button>
        </form>
        <form method="POST" action="{{ route('admin.delivery-imports.invoice-status.update', $batch) }}" class="d-inline" onsubmit="return confirm('Fatura durumunu «Fatura Oluşturuldu» olarak güncellemek istediğinize emin misiniz?');">
            @csrf
            @method('PATCH')
            <input type="hidden" name="invoice_status" value="created">
            <input type="hidden" name="back" value="veri-analiz-raporu">
            <button type="submit" class="btn {{ ($batch->invoice_status ?? null) === 'created' ? 'btn-info' : 'btn-outline-info' }} btn-sm">Fatura Oluşturuldu</button>
        </form>
        <form method="POST" action="{{ route('admin.delivery-imports.invoice-status.update', $batch) }}" class="d-inline" onsubmit="return confirm('Fatura durumunu «Gönderildi» olarak güncellemek istediğinize emin misiniz?');">
            @csrf
            @method('PATCH')
            <input type="hidden" name="invoice_status" value="sent">
            <input type="hidden" name="back" value="veri-analiz-raporu">
            <button type="submit" class="btn {{ ($batch->invoice_status ?? null) === 'sent' ? 'btn-success' : 'btn-outline-success' }} btn-sm">Gönderildi</button>
        </form>
        @if($batch->invoice_status ?? null)
            <span class="badge bg-secondary ms-2">Mevcut: {{ match($batch->invoice_status) { 'pending' => 'Fatura Beklemede', 'created' => 'Fatura Oluşturuldu', 'sent' => 'Gönderildi', default => $batch->invoice_status } }}</span>
        @endif
    </div>
</div>
@endif
@endsection
