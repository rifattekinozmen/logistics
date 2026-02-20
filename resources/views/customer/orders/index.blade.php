@extends('layouts.customer-app')

@section('title', 'Siparişlerim - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">shopping_cart</span>
            <h2 class="h3 fw-bold text-dark mb-0">Siparişlerim</h2>
        </div>
        <p class="text-secondary mb-0">Tüm siparişlerinizi görüntüleyin ve takip edin</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customer.orders.index', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
            CSV İndir
        </a>
        @if(Auth::user() && Auth::user()->hasPermission('customer.portal.orders.create'))
            <a href="{{ route('customer.orders.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
                Yeni Sipariş
            </a>
        @endif
    </div>
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('customer.orders.index') }}" class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Atandı</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Yolda</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Teslim Edildi</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Sipariş No</th>
                    <th class="border-0 small text-secondary fw-semibold">Durum</th>
                    <th class="border-0 small text-secondary fw-semibold">Teslimat Adresi</th>
                    <th class="border-0 small text-secondary fw-semibold">Planlanan Tarih</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $order->order_number }}</span>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ match($order->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }}-200 text-{{ match($order->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }} rounded-pill px-3 py-2">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ Str::limit($order->delivery_address, 40) }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $order->planned_delivery_date?->format('d.m.Y') ?? '-' }}
                            </small>
                        </td>
                        <td class="align-middle text-end">
                            <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm bg-primary-200 text-primary border-0">
                                Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">shopping_cart</span>
                            <p class="text-secondary mb-0">Henüz sipariş bulunmuyor.</p>
                            <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-sm mt-3 d-inline-flex align-items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">add</span>
                                İlk Siparişi Oluştur
                            </a>
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
