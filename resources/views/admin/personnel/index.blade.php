@extends('layouts.app')

@section('title', 'Personel - Logistics')
@section('page-title', 'Personel')
@section('page-subtitle', 'Tüm personeli görüntüleyin ve yönetin')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3xl" role="alert">
    <span class="material-symbols-outlined me-2">check_circle</span>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">groups</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Personel</h2>
            <p class="text-secondary mb-0">Tüm personeli görüntüleyin ve yönetin</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.personnel.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Personel
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="groups" color="primary" col="col-md-6" />
    <x-index-stat-card title="Aktif" :value="$stats['active'] ?? 0" icon="check_circle" color="success" col="col-md-6" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.personnel.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="aktif" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('aktif') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('aktif') == '0' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Departman</label>
            <select name="departman" id="filter-departman" class="form-select">
                <option value="">Tümü</option>
                @foreach(($departments ?? []) as $key => $label)
                    <option value="{{ $key }}" {{ request('departman') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Pozisyon</label>
            <select name="pozisyon" id="filter-pozisyon" class="form-select">
                <option value="">Tümü</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="personnel-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="activate">Aktif yap</option>
                <option value="deactivate">Pasif yap</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="personnel-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="personnel-selected-count">0</span> kayıt seçili
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
                        <input type="checkbox" id="select-all-personnel">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'ad_soyad' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.personnel.index', array_merge(request()->query(), ['sort' => 'ad_soyad', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Ad Soyad</span>
                            @if($currentSort === 'ad_soyad')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'email' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.personnel.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Email</span>
                            @if($currentSort === 'email')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'telefon' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.personnel.index', array_merge(request()->query(), ['sort' => 'telefon', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Telefon</span>
                            @if($currentSort === 'telefon')<span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>@else<span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>@endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'departman' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.personnel.index', array_merge(request()->query(), ['sort' => 'departman', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Departman</span>
                            @if($currentSort === 'departman')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'pozisyon' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.personnel.index', array_merge(request()->query(), ['sort' => 'pozisyon', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Pozisyon</span>
                            @if($currentSort === 'pozisyon')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'ise_baslama_tarihi' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.personnel.index', array_merge(request()->query(), ['sort' => 'ise_baslama_tarihi', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>İşe Başlama</span>
                            @if($currentSort === 'ise_baslama_tarihi')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'maas' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.personnel.index', array_merge(request()->query(), ['sort' => 'maas', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Maaş</span>
                            @if($currentSort === 'maas')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php $direction = $currentSort === 'aktif' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.personnel.index', array_merge(request()->query(), ['sort' => 'aktif', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Durum</span>
                            @if($currentSort === 'aktif')
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
                @forelse($personels as $personel)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="personnel-row-checkbox" value="{{ $personel->id }}">
                    </td>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $personel->ad_soyad }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->email ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->telefon ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->departman ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->pozisyon ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->ise_baslama_tarihi ? $personel->ise_baslama_tarihi->format('d.m.Y') : '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $personel->maas ? number_format($personel->maas, 2, ',', '.') . ' ₺' : '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $personel->aktif ? 'primary-200 text-primary' : 'secondary-200 text-secondary' }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $personel->aktif ? 'Aktif' : 'Pasif' }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.personnel.show', $personel->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.personnel.edit', $personel->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.personnel.destroy', $personel->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
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
                    <td colspan="11" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">groups</span>
                            <p class="text-secondary mb-0">Henüz personel bulunmuyor.</p>
                            <a href="{{ route('admin.personnel.create') }}" class="btn btn-primary btn-sm mt-2">İlk Personeli Ekle</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($personels->hasPages())
    <div class="p-4 border-top">
        {{ $personels->links() }}
    </div>
    @endif
</div>

<form id="personnel-bulk-form" method="POST" action="{{ route('admin.personnel.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="personnel-bulk-action-input">
</form>
@endsection

@push('scripts')
<script>
const perMaster = document.getElementById('select-all-personnel');
const perRows = document.querySelectorAll('.personnel-row-checkbox');
const perCountEl = document.getElementById('personnel-selected-count');
const perApplyBtn = document.getElementById('personnel-bulk-apply');
const perActionSelect = document.getElementById('personnel-bulk-action');
const perForm = document.getElementById('personnel-bulk-form');
const perActionInput = document.getElementById('personnel-bulk-action-input');

function updatePersonnelSelectedCount() {
    const selected = Array.from(perRows).filter(cb => cb.checked);
    if (perCountEl) perCountEl.textContent = selected.length.toString();
    if (perMaster) {
        perMaster.checked = selected.length > 0 && selected.length === perRows.length;
        perMaster.indeterminate = selected.length > 0 && selected.length < perRows.length;
    }
}
if (perMaster) {
    perMaster.addEventListener('change', function () {
        perRows.forEach(function (cb) { cb.checked = perMaster.checked; });
        updatePersonnelSelectedCount();
    });
}
perRows.forEach(function (cb) { cb.addEventListener('change', updatePersonnelSelectedCount); });
if (perApplyBtn) {
    perApplyBtn.addEventListener('click', function () {
        const action = perActionSelect.value;
        const selected = Array.from(perRows).filter(cb => cb.checked);
        if (!action) { alert('Lütfen bir toplu işlem seçin.'); return; }
        if (selected.length === 0) { alert('Lütfen en az bir kayıt seçin.'); return; }
        if (action === 'delete' && !confirm('Seçili personeli silmek istediğinize emin misiniz?')) return;
        perForm.querySelectorAll('input[name="selected[]"]').forEach(function (input) { input.remove(); });
        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden'; hidden.name = 'selected[]'; hidden.value = cb.value;
            perForm.appendChild(hidden);
        });
        perActionInput.value = action;
        perForm.submit();
    });
}
// Departman - Pozisyon filtre bağlama
document.addEventListener('DOMContentLoaded', function () {
    const positionMap = @json($position_map ?? []);
    const deptSelect = document.getElementById('filter-departman');
    const posSelect = document.getElementById('filter-pozisyon');

    if (!deptSelect || !posSelect) {
        return;
    }

    const selectedPos = @json(request('pozisyon'));

    function fillFilterPositions(department) {
        posSelect.innerHTML = '<option value="">Tümü</option>';
        if (!department || !positionMap[department]) {
            return;
        }
        positionMap[department].forEach(function (name) {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            if (selectedPos && selectedPos === name) {
                opt.selected = true;
            }
            posSelect.appendChild(opt);
        });
    }

    fillFilterPositions(deptSelect.value || null);

    deptSelect.addEventListener('change', function () {
        fillFilterPositions(this.value || null);
        posSelect.value = '';
    });
});
</script>
@endpush
