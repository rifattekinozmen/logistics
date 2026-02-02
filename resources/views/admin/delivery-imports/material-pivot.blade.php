@extends('layouts.app')

@section('title', 'Malzeme Pivot Tablosu - Logistics')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Malzeme Pivot Tablosu</h2>
        <p class="text-secondary mb-0">
            Dosya: <span class="fw-semibold">{{ $batch->file_name }}</span>
            @if($reportTypeLabel ?? null)
                <span class="ms-2 badge bg-primary-200 text-primary rounded-pill px-2 py-1 small">{{ $reportTypeLabel }}</span>
            @endif
        </p>
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

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden w-100" style="max-width: 100%;">
    <div class="w-100" style="overflow: visible;">
        <table class="table table-bordered table-hover table-sm mb-0 w-100 text-center" style="table-layout: fixed; font-size: 0.875rem;">
            <thead>
                <tr style="background-color: #e7f1ff;">
                    <th class="border-0 text-secondary fw-semibold py-2 px-2" style="width: 5rem; white-space: normal; background-color: #e7f1ff;" title="Tarih (gg.aa.yyyy)">TARİH</th>
                    @foreach($pivot['materials'] as $m)
                        @php
                            $parts = explode(' | ', $m['label'], 2);
                            $code = $parts[0] ?? $m['label'];
                            $text = $parts[1] ?? '';
                        @endphp
                        <th class="border-0 text-secondary fw-semibold py-2 px-2 align-middle" style="white-space: normal; min-width: 0; line-height: 1.3; background-color: #e7f1ff;" title="{{ $m['label'] }}">
                            <div class="d-flex flex-column gap-0">
                                <span class="small text-dark">{{ $code }}</span>
                                @if($text !== '')
                                    <span class="small text-secondary">{{ $text }}</span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    <th class="border-0 text-secondary fw-semibold py-2 px-2 align-middle" style="white-space: normal; background-color: #f0f4f8;">TOPLAM</th>
                    <th class="border-0 text-secondary fw-semibold py-2 px-2 align-middle text-primary" style="white-space: normal; min-width: 5rem; background-color: #cfe2ff;">
                        <div class="d-flex flex-column gap-0 lh-sm">
                            <span>BOŞ-DOLU TAŞINAN</span>
                            <span>GEÇERLİ MİKTAR</span>
                        </div>
                    </th>
                    <th class="border-0 text-secondary fw-semibold py-2 px-2 align-middle" style="background-color: #d1e7dd; color: #0f5132; white-space: normal; min-width: 5rem;">
                        <div class="d-flex flex-column gap-0 lh-sm">
                            <span>DOLU-DOLU TAŞINAN</span>
                            <span>GEÇERLİ MİKTAR</span>
                        </div>
                    </th>
                    <th class="border-0 text-secondary fw-semibold py-2 px-2 align-middle" style="white-space: normal; min-width: 5rem; background-color: #f0f4f8;">
                        <div class="d-flex flex-column gap-0 lh-sm">
                            <span>BOŞ-DOLU TAŞINAN</span>
                            <span>MALZEME KISA METNİ</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse(($pivot['rows'] ?? []) as $row)
                    <tr>
                        <td class="fw-semibold py-2 px-2 align-middle">{{ $row['tarih'] }}</td>
                        @foreach($pivot['materials'] as $m)
                            <td class="py-2 px-2 align-middle" style="min-width: 0;">{{ number_format($row['material_totals'][$m['key']] ?? 0, 2, ',', '.') }}</td>
                        @endforeach
                        <td class="fw-semibold py-2 px-2 align-middle" style="background-color: #f0f4f8;">{{ number_format($row['row_total'], 2, ',', '.') }}</td>
                        <td class="fw-semibold text-primary py-2 px-2 align-middle" style="background-color: #cfe2ff;">{{ number_format($row['boş_dolu'], 2, ',', '.') }}</td>
                        <td class="fw-semibold py-2 px-2 align-middle" style="background-color: #d1e7dd; color: #0f5132;">{{ number_format($row['dolu_dolu'], 2, ',', '.') }}</td>
                        <td class="py-2 px-2 align-middle" style="white-space: normal; min-width: 0; background-color: #f8f9fa;">{{ Str::limit($row['malzeme_kisa_metni'], 12) }}</td>
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
                    <td class="py-2 px-2 align-middle" style="background-color: #cfe2ff;">Toplam</td>
                    @foreach($pivot['materials'] as $m)
                        <td class="py-2 px-2 align-middle" style="background-color: #e7f1ff;">{{ number_format($pivot['totals_row']['material_totals'][$m['key']] ?? 0, 2, ',', '.') }}</td>
                    @endforeach
                    <td class="py-2 px-2 align-middle" style="background-color: #cfe2ff;">{{ number_format($pivot['totals_row']['row_total'], 2, ',', '.') }}</td>
                    <td class="text-primary py-2 px-2 align-middle fw-bold" style="background-color: #cfe2ff;">{{ number_format($pivot['totals_row']['boş_dolu'], 2, ',', '.') }}</td>
                    <td class="py-2 px-2 align-middle fw-bold" style="background-color: #d1e7dd; color: #0f5132;">{{ number_format($pivot['totals_row']['dolu_dolu'], 2, ',', '.') }}</td>
                    <td class="py-2 px-2 align-middle" style="background-color: #cfe2ff;">-</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
