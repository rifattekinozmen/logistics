@extends('layouts.app')

@section('title', 'Personel - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel</h2>
        <p class="text-secondary mb-0">Tüm personeli görüntüleyin ve yönetin</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                Dışa Aktar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.employees.index', array_merge(request()->query(), ['export' => 'csv'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">table_chart</span>
                        CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.employees.index', array_merge(request()->query(), ['export' => 'xml'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">code</span>
                        XML
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-employees d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Personel
        </a>
    </div>
</div>

<div class="filter-area filter-area-employees rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.employees.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pasif</option>
                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>İzinli</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Şube</label>
            <select name="branch_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($branches ?? [] as $branch)
                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Pozisyon</label>
            <select name="position_id" class="form-select">
                <option value="">Tümü</option>
                @foreach($positions ?? [] as $position)
                <option value="{{ $position->id }}" {{ request('position_id') == $position->id ? 'selected' : '' }}>
                    {{ $position->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-employees w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="employees-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="activate">Aktif yap</option>
                <option value="deactivate">Pasif yap</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="employees-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="employees-selected-count">0</span> kayıt seçili
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
                        <input type="checkbox" id="select-all-employees">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'employee_number' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.employees.index', array_merge(request()->query(), ['sort' => 'employee_number', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Personel No</span>
                            @if($currentSort === 'employee_number')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'first_name' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.employees.index', array_merge(request()->query(), ['sort' => 'first_name', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Ad Soyad</span>
                            @if($currentSort === 'first_name')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">Şube</th>
                    <th class="border-0 fw-semibold text-secondary small">Pozisyon</th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'hire_date' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.employees.index', array_merge(request()->query(), ['sort' => 'hire_date', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>İşe Başlama</span>
                            @if($currentSort === 'hire_date')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.employees.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Durum</span>
                            @if($currentSort === 'status')
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
                @forelse($employees as $employee)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="employees-row-checkbox" value="{{ $employee->id }}">
                    </td>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $employee->employee_number }}</span>
                    </td>
                    <td class="align-middle">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $employee->branch->name ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $employee->position->name ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $employee->hire_date ? $employee->hire_date->format('d.m.Y') : '-' }}</small>
                    </td>
                    <td class="align-middle">
                        @php
                            $statusColors = [0 => 'secondary', 1 => 'success', 2 => 'warning'];
                            $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'İzinli'];
                            $color = $statusColors[$employee->status] ?? 'secondary';
                            $label = $statusLabels[$employee->status] ?? '-';
                            $softColors = ['secondary' => 'secondary-200', 'success' => 'success-200', 'warning' => 'warning-200'];
                            $softColor = $softColors[$color] ?? 'secondary-200';
                        @endphp
                        <span class="badge bg-{{ $softColor }} text-{{ $color }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
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
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">groups</span>
                            <p class="text-secondary mb-0">Henüz personel bulunmuyor.</p>
                            <a href="{{ route('admin.employees.create') }}" class="btn btn-employees btn-sm mt-2">İlk Personeli Ekle</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="p-4 border-top">
        {{ $employees->links() }}
    </div>
    @endif
</div>

<form id="employees-bulk-form" method="POST" action="{{ route('admin.employees.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="employees-bulk-action-input">
</form>
@endsection

@push('scripts')
<script>
const empMaster = document.getElementById('select-all-employees');
const empRows = document.querySelectorAll('.employees-row-checkbox');
const empCountEl = document.getElementById('employees-selected-count');
const empApplyBtn = document.getElementById('employees-bulk-apply');
const empActionSelect = document.getElementById('employees-bulk-action');
const empForm = document.getElementById('employees-bulk-form');
const empActionInput = document.getElementById('employees-bulk-action-input');

function updateEmployeesSelectedCount() {
    const selected = Array.from(empRows).filter(cb => cb.checked);
    if (empCountEl) empCountEl.textContent = selected.length.toString();
    if (empMaster) {
        empMaster.checked = selected.length > 0 && selected.length === empRows.length;
        empMaster.indeterminate = selected.length > 0 && selected.length < empRows.length;
    }
}
if (empMaster) {
    empMaster.addEventListener('change', function () {
        empRows.forEach(function (cb) { cb.checked = empMaster.checked; });
        updateEmployeesSelectedCount();
    });
}
empRows.forEach(function (cb) { cb.addEventListener('change', updateEmployeesSelectedCount); });
if (empApplyBtn) {
    empApplyBtn.addEventListener('click', function () {
        const action = empActionSelect.value;
        const selected = Array.from(empRows).filter(cb => cb.checked);
        if (!action) { alert('Lütfen bir toplu işlem seçin.'); return; }
        if (selected.length === 0) { alert('Lütfen en az bir kayıt seçin.'); return; }
        if (action === 'delete' && !confirm('Seçili personeli silmek istediğinize emin misiniz?')) return;
        empForm.querySelectorAll('input[name="selected[]"]').forEach(function (input) { input.remove(); });
        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden'; hidden.name = 'selected[]'; hidden.value = cb.value;
            empForm.appendChild(hidden);
        });
        empActionInput.value = action;
        empForm.submit();
    });
}
</script>
@endpush
