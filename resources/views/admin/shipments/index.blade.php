@extends('layouts.app')

@section('title', 'Sevkiyatlar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">inventory_2</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Sevkiyatlar</h2>
            <p class="text-secondary mb-0">Tüm sevkiyatları görüntüleyin ve yönetin</p>
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
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="shipments-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="shipments-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="shipments-selected-count">0</span> kayıt seçili
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
                        <input type="checkbox" id="select-all-shipments">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'id' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.shipments.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Sevkiyat No</span>
                            @if($currentSort === 'id')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">Sipariş</th>
                    <th class="border-0 fw-semibold text-secondary small">Araç</th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.shipments.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}"
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
                        @php
                            $direction = $currentSort === 'pickup_date' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.shipments.index', array_merge(request()->query(), ['sort' => 'pickup_date', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Alış Tarihi</span>
                            @if($currentSort === 'pickup_date')
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
                @forelse($shipments as $shipment)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="shipments-row-checkbox" value="{{ $shipment->id }}">
                    </td>
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
                    <td colspan="7" class="text-center py-5">
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

<form id="shipments-bulk-form" method="POST" action="{{ route('admin.shipments.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="shipments-bulk-action-input">
</form>
@endsection

@push('scripts')
<script>
const shMaster = document.getElementById('select-all-shipments');
const shRows = document.querySelectorAll('.shipments-row-checkbox');
const shCountEl = document.getElementById('shipments-selected-count');
const shApplyBtn = document.getElementById('shipments-bulk-apply');
const shActionSelect = document.getElementById('shipments-bulk-action');
const shForm = document.getElementById('shipments-bulk-form');
const shActionInput = document.getElementById('shipments-bulk-action-input');

function updateShipmentsSelectedCount() {
    const selected = Array.from(shRows).filter(cb => cb.checked);
    if (shCountEl) {
        shCountEl.textContent = selected.length.toString();
    }
    if (shMaster) {
        shMaster.checked = selected.length > 0 && selected.length === shRows.length;
        shMaster.indeterminate = selected.length > 0 && selected.length < shRows.length;
    }
}

if (shMaster) {
    shMaster.addEventListener('change', function () {
        const checked = shMaster.checked;
        shRows.forEach(function (cb) {
            cb.checked = checked;
        });
        updateShipmentsSelectedCount();
    });
}

shRows.forEach(function (cb) {
    cb.addEventListener('change', updateShipmentsSelectedCount);
});

if (shApplyBtn) {
    shApplyBtn.addEventListener('click', function () {
        const action = shActionSelect.value;
        const selected = Array.from(shRows).filter(cb => cb.checked);

        if (! action) {
            alert('Lütfen bir toplu işlem seçin.');
            return;
        }

        if (selected.length === 0) {
            alert('Lütfen en az bir kayıt seçin.');
            return;
        }

        if (action === 'delete' && ! confirm('Seçili sevkiyatları silmek istediğinize emin misiniz?')) {
            return;
        }

        shForm.querySelectorAll('input[name="selected[]"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected[]';
            hidden.value = cb.value;
            shForm.appendChild(hidden);
        });

        shActionInput.value = action;
        shForm.submit();
    });
}
</script>
@endpush
