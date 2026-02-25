@extends('layouts.app')

@section('title', 'İş Ortakları (Business Partners) - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">handshake</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">İş Ortakları</h2>
            <p class="text-secondary mb-0">SAP Business Partner uyumlu müşteri ve tedarikçi yönetimi</p>
        </div>
    </div>
    <a href="{{ route('admin.business-partners.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni İş Ortağı
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="handshake" color="primary" col="col-md-6" />
    <x-index-stat-card title="Aktif" :value="$stats['active'] ?? 0" icon="check_circle" color="success" col="col-md-6" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.business-partners.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Tür</label>
            <select name="partner_type" class="form-select">
                <option value="">Tümü</option>
                <option value="customer" {{ request('partner_type') === 'customer' ? 'selected' : '' }}>Müşteri</option>
                <option value="vendor"   {{ request('partner_type') === 'vendor'   ? 'selected' : '' }}>Tedarikçi</option>
                <option value="carrier"  {{ request('partner_type') === 'carrier'  ? 'selected' : '' }}>Taşıyıcı</option>
                <option value="both"     {{ request('partner_type') === 'both'     ? 'selected' : '' }}>Müşteri & Tedarikçi</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-dark">Arama</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ad, BP no veya vergi no...">
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
                    <th class="border-0 fw-semibold text-secondary small">BP No</th>
                    <th class="border-0 fw-semibold text-secondary small">Ad</th>
                    <th class="border-0 fw-semibold text-secondary small">Tür</th>
                    <th class="border-0 fw-semibold text-secondary small">Vergi No</th>
                    <th class="border-0 fw-semibold text-secondary small">Para Birimi</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($partners as $partner)
                @php
                    $typeLabels = ['customer' => 'Müşteri', 'vendor' => 'Tedarikçi', 'carrier' => 'Taşıyıcı', 'both' => 'Müşteri & Tedarikçi'];
                    $typeColors = ['customer' => 'primary', 'vendor' => 'warning', 'carrier' => 'info', 'both' => 'success'];
                    $typeColor  = $typeColors[$partner->partner_type] ?? 'secondary';
                @endphp
                <tr>
                    <td class="align-middle"><span class="fw-bold font-monospace text-dark">{{ $partner->partner_number }}</span></td>
                    <td class="align-middle fw-semibold text-dark">{{ $partner->name }}</td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $typeColor }}-200 text-{{ $typeColor }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $typeLabels[$partner->partner_type] ?? $partner->partner_type }}
                        </span>
                    </td>
                    <td class="align-middle"><small class="text-secondary">{{ $partner->tax_number ?? '-' }}</small></td>
                    <td class="align-middle"><small class="text-secondary">{{ $partner->currency }}</small></td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $partner->status ? 'success' : 'danger' }}-200 text-{{ $partner->status ? 'success' : 'danger' }} px-3 py-2 rounded-pill">
                            {{ $partner->status ? 'Aktif' : 'Pasif' }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.business-partners.show', $partner->id) }}" class="btn btn-sm bg-info-200 text-info border-0" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.business-partners.edit', $partner->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.business-partners.destroy', $partner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu iş ortağını silmek istediğinize emin misiniz?')">
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
                    <td colspan="7" class="text-center py-5">
                        <span class="material-symbols-outlined text-secondary d-block mb-2" style="font-size: 3rem;">handshake</span>
                        <p class="text-secondary mb-0">Henüz iş ortağı bulunmuyor.</p>
                        <a href="{{ route('admin.business-partners.create') }}" class="btn btn-primary btn-sm mt-2">İlk İş Ortağını Oluştur</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($partners->hasPages())
    <div class="p-4 border-top">{{ $partners->links() }}</div>
    @endif
</div>
@endsection
