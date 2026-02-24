@extends('layouts.app')

@section('title', 'Siparişler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Siparişler</h2>
        <p class="text-secondary mb-0">Tüm siparişleri görüntüleyin ve yönetin</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                Dışa Aktar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.orders.index', array_merge(request()->query(), ['export' => 'csv'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">table_chart</span>
                        CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.orders.index', array_merge(request()->query(), ['export' => 'xml'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">code</span>
                        XML
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.orders.import') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">upload_file</span>
            İçe Aktar
        </a>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-orders d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Sipariş
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="shopping_cart" color="primary" />
    <x-index-stat-card title="Bekleyen" :value="$stats['pending'] ?? 0" icon="schedule" color="warning" />
    <x-index-stat-card title="Teslim Edilen" :value="$stats['delivered'] ?? 0" icon="local_shipping" color="success" />
    <x-index-stat-card title="İptal" :value="$stats['cancelled'] ?? 0" icon="cancel" color="danger" />
</div>

<div class="filter-area filter-area-orders rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>Planlandı</option>
                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Atandı</option>
                <option value="loaded" {{ request('status') === 'loaded' ? 'selected' : '' }}>Yüklendi</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Yolda</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Teslim Edildi</option>
                <option value="invoiced" {{ request('status') === 'invoiced' ? 'selected' : '' }}>Faturalandı</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>İptal</option>
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
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-orders w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

    <div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Sipariş No</th>
                    <th class="border-0 fw-semibold text-secondary small">Müşteri</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small">Alış Adresi</th>
                    <th class="border-0 fw-semibold text-secondary small">Teslimat Tarihi</th>
                    <th class="border-0 fw-semibold text-secondary small">Ağırlık</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $order->order_number }}</span>
                    </td>
                    <td class="align-middle">
                        {{ $order->customer->name ?? '-' }}
                    </td>
                    <td class="align-middle">
                        @php
                            $statusColors = [
                                'pending'    => 'warning',
                                'planned'    => 'info',
                                'assigned'   => 'info',
                                'loaded'     => 'primary',
                                'in_transit' => 'primary',
                                'delivered'  => 'success',
                                'invoiced'   => 'success',
                                'cancelled'  => 'danger',
                            ];
                            $softColors = [
                                'warning' => 'warning-200',
                                'info'    => 'info-200',
                                'primary' => 'primary-200',
                                'success' => 'success-200',
                                'danger'  => 'danger-200',
                            ];
                            $color = $statusColors[$order->status] ?? 'secondary';
                            $softColor = $softColors[$color] ?? 'secondary-200';
                        @endphp
                        <span class="badge bg-{{ $softColor }} text-{{ $color }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ Str::limit($order->pickup_address, 30) }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $order->planned_delivery_date ? $order->planned_delivery_date->format('d.m.Y') : '-' }}
                        </small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $order->total_weight ? number_format($order->total_weight, 2).' kg' : '-' }}</small>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu siparişi silmek istediğinize emin misiniz?');">
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
                    <td colspan="7" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">shopping_cart</span>
                            <p class="text-secondary mb-0">Henüz sipariş bulunmuyor.</p>
                            <a href="{{ route('admin.orders.create') }}" class="btn btn-orders btn-sm mt-2">İlk Siparişi Oluştur</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="p-4 border-top">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
