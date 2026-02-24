@extends('layouts.app')

@section('title', 'Motorin Fiyatları - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Motorin Fiyatları</h2>
        <p class="text-secondary mb-0">Günlük motorin fiyat kayıtlarını görüntüleyin</p>
    </div>
    <a href="{{ route('admin.fuel-prices.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Fiyat Kaydı
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam Kayıt" :value="$stats['total'] ?? 0" icon="local_gas_station" color="primary" col="col-md-12" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.fuel-prices.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Fiyat Türü</label>
            <select name="price_type" class="form-select">
                <option value="">Tümü</option>
                <option value="purchase" {{ request('price_type') === 'purchase' ? 'selected' : '' }}>Satın Alma</option>
                <option value="station" {{ request('price_type') === 'station' ? 'selected' : '' }}>İstasyon</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Tarih Başlangıç</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Tarih Bitiş</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Tarih</th>
                    <th class="border-0 small text-secondary fw-semibold">Fiyat Türü</th>
                    <th class="border-0 small text-secondary fw-semibold">Fiyat (TL/Litre)</th>
                    <th class="border-0 small text-secondary fw-semibold">Tedarikçi/Bölge</th>
                    <th class="border-0 small text-secondary fw-semibold">Notlar</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prices as $price)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $price->price_date->format('d.m.Y') }}</span>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ $price->price_type === 'purchase' ? 'info' : 'warning' }}-200 text-{{ $price->price_type === 'purchase' ? 'info' : 'warning' }} rounded-pill px-3 py-2">
                                {{ $price->price_type === 'purchase' ? 'Satın Alma' : 'İstasyon' }}
                            </span>
                        </td>
                        <td class="align-middle">
                            <span class="fw-bold text-dark">{{ number_format($price->price, 4) }} ₺</span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $price->supplier_name ?? '-' }}
                                @if($price->region)
                                    <br><span class="text-muted">({{ $price->region }})</span>
                                @endif
                            </small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ Str::limit($price->notes ?? '-', 30) }}</small>
                        </td>
                        <td class="align-middle text-end">
                            <a href="{{ route('admin.fuel-prices.show', $price) }}" class="btn btn-sm bg-primary-200 text-primary border-0">
                                Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">local_gas_station</span>
                                <p class="text-secondary mb-0">Henüz motorin fiyat kaydı bulunmuyor.</p>
                                <a href="{{ route('admin.fuel-prices.create') }}" class="btn btn-primary btn-sm mt-2">İlk Fiyatı Kaydet</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($prices->hasPages())
        <div class="p-4 border-top">
            {{ $prices->links() }}
        </div>
    @endif
</div>
@endsection
