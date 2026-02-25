@extends('layouts.app')

@section('title', 'Fiyatlandırma Koşulları - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">price_check</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Fiyatlandırma Koşulları</h2>
            <p class="text-secondary mb-0">SAP uyumlu navlun fiyat hesaplama koşulları</p>
        </div>
    </div>
    <a href="{{ route('admin.pricing-conditions.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Koşul
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="price_check" color="primary" col="col-md-6" />
    <x-index-stat-card title="Aktif" :value="$stats['active'] ?? 0" icon="check_circle" color="success" col="col-md-6" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.pricing-conditions.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Koşul Türü</label>
            <select name="condition_type" class="form-select">
                <option value="">Tümü</option>
                <option value="weight_based"   {{ request('condition_type') === 'weight_based'   ? 'selected' : '' }}>Ağırlık Bazlı</option>
                <option value="distance_based" {{ request('condition_type') === 'distance_based' ? 'selected' : '' }}>Mesafe Bazlı</option>
                <option value="flat"           {{ request('condition_type') === 'flat'           ? 'selected' : '' }}>Sabit Ücret</option>
                <option value="zone_based"     {{ request('condition_type') === 'zone_based'     ? 'selected' : '' }}>Bölge Bazlı</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Koşul Adı</th>
                    <th class="border-0 fw-semibold text-secondary small">Tür</th>
                    <th class="border-0 fw-semibold text-secondary small">Güzergah</th>
                    <th class="border-0 fw-semibold text-secondary small">Fiyat</th>
                    <th class="border-0 fw-semibold text-secondary small">Para Birimi</th>
                    <th class="border-0 fw-semibold text-secondary small">Geçerlilik</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conditions as $condition)
                @php
                    $typeLabels = ['weight_based' => 'Ağırlık', 'distance_based' => 'Mesafe', 'flat' => 'Sabit', 'zone_based' => 'Bölge'];
                    $typeColors = ['weight_based' => 'info', 'distance_based' => 'primary', 'flat' => 'success', 'zone_based' => 'warning'];
                    $typeColor  = $typeColors[$condition->condition_type] ?? 'secondary';

                    $priceText = match($condition->condition_type) {
                        'weight_based'   => number_format($condition->price_per_kg, 2).'/'.'kg',
                        'distance_based' => number_format($condition->price_per_km, 2).'/'.'km',
                        'flat','zone_based' => number_format($condition->flat_rate, 2),
                        default => '-',
                    };
                @endphp
                <tr>
                    <td class="align-middle fw-semibold text-dark">{{ $condition->name }}</td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $typeColor }}-200 text-{{ $typeColor }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $typeLabels[$condition->condition_type] ?? $condition->condition_type }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $condition->route_origin ?? '*' }} → {{ $condition->route_destination ?? '*' }}
                        </small>
                    </td>
                    <td class="align-middle"><span class="fw-semibold">{{ $priceText }}</span></td>
                    <td class="align-middle"><small class="text-secondary">{{ $condition->currency }}</small></td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $condition->valid_from ? $condition->valid_from->format('d.m.Y') : '∞' }}
                            —
                            {{ $condition->valid_to ? $condition->valid_to->format('d.m.Y') : '∞' }}
                        </small>
                    </td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $condition->status ? 'success' : 'danger' }}-200 text-{{ $condition->status ? 'success' : 'danger' }} px-3 py-2 rounded-pill">
                            {{ $condition->status ? 'Aktif' : 'Pasif' }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.pricing-conditions.edit', $condition->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.pricing-conditions.destroy', $condition->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu fiyatlandırma koşulunu silmek istediğinize emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0" title="Sil">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <span class="material-symbols-outlined text-secondary d-block mb-2" style="font-size: 3rem;">price_change</span>
                        <p class="text-secondary mb-0">Henüz fiyatlandırma koşulu bulunmuyor.</p>
                        <a href="{{ route('admin.pricing-conditions.create') }}" class="btn btn-primary btn-sm mt-2">İlk Koşulu Oluştur</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($conditions->hasPages())
    <div class="p-4 border-top">{{ $conditions->links() }}</div>
    @endif
</div>
@endsection
