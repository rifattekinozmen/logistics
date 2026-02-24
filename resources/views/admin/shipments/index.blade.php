@extends('layouts.app')

@section('title', 'Sevkiyatlar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Sevkiyatlar</h2>
        <p class="text-secondary mb-0">Tüm sevkiyatları görüntüleyin ve yönetin</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                Dışa Aktar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.shipments.index', array_merge(request()->query(), ['export' => 'csv'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">table_chart</span>
                        CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.shipments.index', array_merge(request()->query(), ['export' => 'xml'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">code</span>
                        XML
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.shipments.create') }}" class="btn btn-shipments d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Sevkiyat
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="inventory_2" color="primary" col="col-md-4" />
    <x-index-stat-card title="Yolda / Beklemede" :value="$stats['active'] ?? 0" icon="schedule" color="warning" col="col-md-4" />
    <x-index-stat-card title="Teslim Edildi" :value="$stats['delivered'] ?? 0" icon="local_shipping" color="success" col="col-md-4" />
</div>

<div class="filter-area filter-area-shipments rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.shipments.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Yolda</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Teslim Edildi</option>
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
            <button type="submit" class="btn btn-filter btn-filter-shipments w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Sevkiyat No</th>
                    <th class="border-0 fw-semibold text-secondary small">Sipariş</th>
                    <th class="border-0 fw-semibold text-secondary small">Araç</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small">Alış Tarihi</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipments as $shipment)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">#{{ $shipment->id }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $shipment->order->order_number ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $shipment->vehicle->plate ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'in_transit' => 'primary',
                                'delivered' => 'success',
                            ];
                            $softColors = [
                                'warning' => 'warning-200',
                                'primary' => 'primary-200',
                                'success' => 'success-200',
                            ];
                            $statusLabels = [
                                'pending' => 'Beklemede',
                                'in_transit' => 'Yolda',
                                'delivered' => 'Teslim Edildi',
                            ];
                            $color = $statusColors[$shipment->status] ?? 'secondary';
                            $softColor = $softColors[$color] ?? 'secondary-200';
                            $label = $statusLabels[$shipment->status] ?? $shipment->status;
                        @endphp
                        <span class="badge bg-{{ $softColor }} text-{{ $color }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $shipment->pickup_date ? $shipment->pickup_date->format('d.m.Y') : '-' }}
                        </small>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.shipments.show', $shipment->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.shipments.edit', $shipment->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.shipments.destroy', $shipment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu sevkiyatı silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0 hover:bg-danger hover:text-white transition-all" title="Sil">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">inventory_2</span>
                            <p class="text-secondary mb-0">Henüz sevkiyat bulunmuyor.</p>
                            <a href="{{ route('admin.shipments.create') }}" class="btn btn-shipments btn-sm mt-2">İlk Sevkiyatı Oluştur</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($shipments->hasPages())
    <div class="p-4 border-top">
        {{ $shipments->links() }}
    </div>
    @endif
</div>
@endsection
