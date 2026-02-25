@extends('layouts.app')

@section('title', 'Siparişler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">shopping_cart</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Siparişler</h2>
            <p class="text-secondary mb-0">Tüm siparişleri görüntüleyin ve yönetin</p>
        </div>
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
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
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
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-orders w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="orders-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="orders-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="orders-selected-count">0</span> kayıt seçili
        </div>
    </div>
    <div class="table-responsive">
        @php
            $currentSort = request('sort');
            $currentDirection = request('direction', 'asc');
        @endphp
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 text-center align-middle" style="width: 40px;">
                        <input type="checkbox" id="select-all-orders">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'order_number' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['sort' => 'order_number', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Sipariş No</span>
                            @if($currentSort === 'order_number')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'customer_name' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['sort' => 'customer_name', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Müşteri</span>
                            @if($currentSort === 'customer_name')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Durum</span>
                            @if($currentSort === 'status')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'pickup_address' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['sort' => 'pickup_address', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Alış Adresi @if($currentSort === 'pickup_address')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'planned_delivery_date' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['sort' => 'planned_delivery_date', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Teslimat Tarihi</span>
                            @if($currentSort === 'planned_delivery_date')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'total_weight' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['sort' => 'total_weight', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Ağırlık</span>
                            @if($currentSort === 'total_weight')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="order-row-checkbox" value="{{ $order->id }}">
                    </td>
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

<form id="orders-bulk-form" method="POST" action="{{ route('admin.orders.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="orders-bulk-action-input">
</form>
@endsection

@push('scripts')
<script>
const orderMasterCheckbox = document.getElementById('select-all-orders');
const orderRowCheckboxes = document.querySelectorAll('.order-row-checkbox');
const orderSelectedCountEl = document.getElementById('orders-selected-count');
const orderBulkApplyBtn = document.getElementById('orders-bulk-apply');
const orderBulkActionSelect = document.getElementById('orders-bulk-action');
const orderBulkForm = document.getElementById('orders-bulk-form');
const orderBulkActionInput = document.getElementById('orders-bulk-action-input');

function updateOrderSelectedCount() {
    const selected = Array.from(orderRowCheckboxes).filter(cb => cb.checked);
    if (orderSelectedCountEl) {
        orderSelectedCountEl.textContent = selected.length.toString();
    }
    if (orderMasterCheckbox) {
        orderMasterCheckbox.checked = selected.length > 0 && selected.length === orderRowCheckboxes.length;
        orderMasterCheckbox.indeterminate = selected.length > 0 && selected.length < orderRowCheckboxes.length;
    }
}

if (orderMasterCheckbox) {
    orderMasterCheckbox.addEventListener('change', function () {
        const checked = orderMasterCheckbox.checked;
        orderRowCheckboxes.forEach(function (cb) {
            cb.checked = checked;
        });
        updateOrderSelectedCount();
    });
}

orderRowCheckboxes.forEach(function (cb) {
    cb.addEventListener('change', updateOrderSelectedCount);
});

if (orderBulkApplyBtn) {
    orderBulkApplyBtn.addEventListener('click', function () {
        const action = orderBulkActionSelect.value;
        const selected = Array.from(orderRowCheckboxes).filter(cb => cb.checked);

        if (! action) {
            alert('Lütfen bir toplu işlem seçin.');
            return;
        }

        if (selected.length === 0) {
            alert('Lütfen en az bir kayıt seçin.');
            return;
        }

        if (action === 'delete' && ! confirm('Seçili siparişleri silmek istediğinize emin misiniz?')) {
            return;
        }

        // Eski hidden input'ları temizle
        orderBulkForm.querySelectorAll('input[name=\"selected[]\"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected[]';
            hidden.value = cb.value;
            orderBulkForm.appendChild(hidden);
        });

        orderBulkActionInput.value = action;
        orderBulkForm.submit();
    });
}
</script>
@endpush
