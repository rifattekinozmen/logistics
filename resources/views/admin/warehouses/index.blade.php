@extends('layouts.app')

@section('title', 'Depolar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">warehouse</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Depolar</h2>
            <p class="text-secondary mb-0">Tüm depoları görüntüleyin ve yönetin</p>
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
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.warehouses.index', array_merge(request()->query(), ['export' => 'csv'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">table_chart</span>
                        CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.warehouses.index', array_merge(request()->query(), ['export' => 'xml'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">code</span>
                        XML
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-warehouses d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Depo
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="warehouse" color="primary" col="col-md-6" />
    <x-index-stat-card title="Aktif" :value="$stats['active'] ?? 0" icon="check_circle" color="success" col="col-md-6" />
</div>

<div class="filter-area filter-area-warehouses rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.warehouses.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-dark">Şube</label>
            <select name="branch_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-warehouses w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
    </div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="warehouses-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="activate">Aktif yap</option>
                <option value="deactivate">Pasif yap</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="warehouses-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="warehouses-selected-count">0</span> kayıt seçili
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
                        <input type="checkbox" id="select-all-warehouses">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'name' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.warehouses.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Depo Adı</span>
                            @if($currentSort === 'name')
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
                            $direction = $currentSort === 'code' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.warehouses.index', array_merge(request()->query(), ['sort' => 'code', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Kod</span>
                            @if($currentSort === 'code')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'branch_id' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.warehouses.index', array_merge(request()->query(), ['sort' => 'branch_id', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Şube @if($currentSort === 'branch_id')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'address' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.warehouses.index', array_merge(request()->query(), ['sort' => 'address', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">Adres @if($currentSort === 'address')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif</a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.warehouses.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}"
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
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="warehouses-row-checkbox" value="{{ $warehouse->id }}">
                    </td>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $warehouse->name }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $warehouse->code ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $warehouse->branch?->name ?? 'Merkez' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ Str::limit($warehouse->address, 30) }}</small>
                    </td>
                    <td class="align-middle">
                        @if($warehouse->status == 1)
                            <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill fw-semibold">Aktif</span>
                        @else
                            <span class="badge bg-danger-200 text-danger px-3 py-2 rounded-pill fw-semibold">Pasif</span>
                        @endif
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu depoyu silmek istediğinize emin misiniz?');">
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
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">warehouse</span>
                            <p class="text-secondary mb-0">Henüz depo bulunmuyor.</p>
                            <a href="{{ route('admin.warehouses.create') }}" class="btn btn-warehouses btn-sm mt-2">İlk Depoyu Oluştur</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($warehouses->hasPages())
    <div class="p-4 border-top">
        {{ $warehouses->links() }}
    </div>
    @endif
</div>

<form id="warehouses-bulk-form" method="POST" action="{{ route('admin.warehouses.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="warehouses-bulk-action-input">
</form>
@endsection

@push('scripts')
<script>
const whMaster = document.getElementById('select-all-warehouses');
const whRows = document.querySelectorAll('.warehouses-row-checkbox');
const whCountEl = document.getElementById('warehouses-selected-count');
const whApplyBtn = document.getElementById('warehouses-bulk-apply');
const whActionSelect = document.getElementById('warehouses-bulk-action');
const whForm = document.getElementById('warehouses-bulk-form');
const whActionInput = document.getElementById('warehouses-bulk-action-input');

function updateWarehousesSelectedCount() {
    const selected = Array.from(whRows).filter(cb => cb.checked);
    if (whCountEl) {
        whCountEl.textContent = selected.length.toString();
    }
    if (whMaster) {
        whMaster.checked = selected.length > 0 && selected.length === whRows.length;
        whMaster.indeterminate = selected.length > 0 && selected.length < whRows.length;
    }
}

if (whMaster) {
    whMaster.addEventListener('change', function () {
        const checked = whMaster.checked;
        whRows.forEach(function (cb) {
            cb.checked = checked;
        });
        updateWarehousesSelectedCount();
    });
}

whRows.forEach(function (cb) {
    cb.addEventListener('change', updateWarehousesSelectedCount);
});

if (whApplyBtn) {
    whApplyBtn.addEventListener('click', function () {
        const action = whActionSelect.value;
        const selected = Array.from(whRows).filter(cb => cb.checked);

        if (! action) {
            alert('Lütfen bir toplu işlem seçin.');
            return;
        }

        if (selected.length === 0) {
            alert('Lütfen en az bir kayıt seçin.');
            return;
        }

        if (action === 'delete' && ! confirm('Seçili depoları silmek istediğinize emin misiniz?')) {
            return;
        }

        whForm.querySelectorAll('input[name="selected[]"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected[]';
            hidden.value = cb.value;
            whForm.appendChild(hidden);
        });

        whActionInput.value = action;
        whForm.submit();
    });
}
</script>
@endpush
