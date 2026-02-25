@extends('layouts.app')

@section('title', 'İş Emirleri - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">build</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">İş Emirleri</h2>
            <p class="text-secondary mb-0">Tüm iş emirlerini görüntüleyin ve yönetin</p>
        </div>
    </div>
    <a href="{{ route('admin.work-orders.create') }}" class="btn btn-work-orders d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni İş Emri
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="build" color="primary" col="col-md-4" />
    <x-index-stat-card title="Bekleyen" :value="$stats['pending'] ?? 0" icon="schedule" color="warning" col="col-md-4" />
    <x-index-stat-card title="Tamamlanan" :value="$stats['completed'] ?? 0" icon="check_circle" color="success" col="col-md-4" />
</div>

<div class="filter-area filter-area-work-orders rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.work-orders.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Onay Bekliyor</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Onaylandı</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Devam Ediyor</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>İptal</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Araç</label>
            <select name="vehicle_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->plate }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Tarih Başlangıç</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-work-orders w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="work-orders-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="work-orders-bulk-apply">Uygula</button>
        </div>
        <div class="small text-secondary"><span id="work-orders-selected-count">0</span> kayıt seçili</div>
    </div>
    <form id="work-orders-bulk-form" action="{{ route('admin.work-orders.bulk') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="action" id="work-orders-bulk-action-input">
    </form>
    <div class="table-responsive">
        @php
            $currentSort = request('sort');
            $currentDirection = request('direction', 'asc');
        @endphp
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 text-center align-middle" style="width: 40px;">
                        <input type="checkbox" id="select-all-work-orders">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'id' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.work-orders.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>İş Emri No</span>
                            @if($currentSort === 'id')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'vehicle_id' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.work-orders.index', array_merge(request()->query(), ['sort' => 'vehicle_id', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Araç</span>
                            @if($currentSort === 'vehicle_id')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'type' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.work-orders.index', array_merge(request()->query(), ['sort' => 'type', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Tür</span>
                            @if($currentSort === 'type')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'service_provider_id' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.work-orders.index', array_merge(request()->query(), ['sort' => 'service_provider_id', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Servis Sağlayıcı</span>
                            @if($currentSort === 'service_provider_id')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.work-orders.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Durum</span>
                            @if($currentSort === 'status')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'created_at' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.work-orders.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Oluşturulma</span>
                            @if($currentSort === 'created_at')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workOrders as $workOrder)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="form-check-input work-order-row-check" name="selected[]" value="{{ $workOrder->id }}" form="work-orders-bulk-form">
                    </td>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">#{{ $workOrder->id }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $workOrder->vehicle->plate ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $workOrder->type }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $workOrder->serviceProvider->name ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'approved' => 'info',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                            $softColors = [
                                'warning' => 'warning-200',
                                'info' => 'info-200',
                                'primary' => 'primary-200',
                                'success' => 'success-200',
                                'danger' => 'danger-200',
                            ];
                            $statusLabels = [
                                'pending' => 'Onay Bekliyor',
                                'approved' => 'Onaylandı',
                                'in_progress' => 'Devam Ediyor',
                                'completed' => 'Tamamlandı',
                                'cancelled' => 'İptal',
                            ];
                            $color = $statusColors[$workOrder->status] ?? 'secondary';
                            $softColor = $softColors[$color] ?? 'secondary-200';
                            $label = $statusLabels[$workOrder->status] ?? $workOrder->status;
                        @endphp
                        <span class="badge bg-{{ $softColor }} text-{{ $color }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $workOrder->created_at ? $workOrder->created_at->format('d.m.Y') : '-' }}
                        </small>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.work-orders.show', $workOrder->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.work-orders.edit', $workOrder->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.work-orders.destroy', $workOrder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu iş emrini silmek istediğinize emin misiniz?');">
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
                    <td colspan="8" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">build</span>
                            <p class="text-secondary mb-0">Henüz iş emri bulunmuyor.</p>
                            <a href="{{ route('admin.work-orders.create') }}" class="btn btn-work-orders btn-sm mt-2">İlk İş Emrini Oluştur</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($workOrders->hasPages())
    <div class="p-4 border-top">
        {{ $workOrders->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('work-orders-bulk-form');
    const actionSelect = document.getElementById('work-orders-bulk-action');
    const actionInput = document.getElementById('work-orders-bulk-action-input');
    const applyBtn = document.getElementById('work-orders-bulk-apply');
    const selectAll = document.getElementById('select-all-work-orders');
    const checkboxes = document.querySelectorAll('.work-order-row-check');
    const countEl = document.getElementById('work-orders-selected-count');
    function updateCount() { const n = document.querySelectorAll('.work-order-row-check:checked').length; countEl.textContent = n; }
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
    if (selectAll) { selectAll.addEventListener('change', function () { checkboxes.forEach(cb => { cb.checked = selectAll.checked; }); updateCount(); }); }
    applyBtn.addEventListener('click', function () {
        const action = actionSelect.value;
        if (!action) return;
        const checked = document.querySelectorAll('.work-order-row-check:checked');
        if (checked.length === 0) { alert('Lütfen en az bir iş emri seçin.'); return; }
        checked.forEach(cb => form.appendChild(cb.cloneNode(true)));
        actionInput.value = action;
        form.submit();
    });
});
</script>
@endpush
@endsection
