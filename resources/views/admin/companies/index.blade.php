@extends('layouts.app')

@section('title', 'Firmalar - Logistics')
@section('page-title', 'Firmalar')
@section('page-subtitle', 'Tüm firmaları görüntüleyin ve yönetin')

@section('navbar-actions')
<a href="{{ route('admin.companies.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
    <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
    Yeni Firma Ekle
</a>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3xl" role="alert">
    <span class="material-symbols-outlined me-2">check_circle</span>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.companies.index') }}" class="row g-3 align-items-end">
        @if(request('sort'))
            <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        @if(request('direction'))
            <input type="hidden" name="direction" value="{{ request('direction') }}">
        @endif
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Ara</label>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="form-control"
                placeholder="Firma adı, kısa isim veya vergi no"
            >
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all d-flex align-items-center justify-content-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">search</span>
                Filtrele
            </button>
        </div>

        @if(request()->hasAny(['search', 'status']) && (request('search') || request('status')))
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">filter_alt_off</span>
                Temizle
            </a>
        </div>
        @endif
    </form>
</div>

@php
    $currentSort = $filters['sort'] ?? '';
    $currentDirection = $filters['direction'] ?? 'asc';
@endphp
<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    @if($companies->isEmpty())
    <div class="text-center py-5">
        <span class="material-symbols-outlined text-secondary mb-3" style="font-size: 64px;">business_center</span>
        <h4 class="h5 fw-bold text-dark mb-2">Henüz firma eklenmemiş</h4>
        <p class="text-secondary mb-4">Sisteme ilk firmayı ekleyerek başlayın.</p>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary d-flex align-items-center gap-2 mx-auto" style="width: fit-content;">
            <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
            Yeni Firma Ekle
        </a>
    </div>
    @else
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="small text-secondary fw-semibold">Sırala:</span>
            @php $q = request()->only(['search', 'status']); @endphp
            <a href="{{ route('admin.companies.index', array_merge($q, ['sort' => 'name', 'direction' => ($currentSort === 'name' && $currentDirection === 'asc') ? 'desc' : 'asc'])) }}" class="btn btn-sm {{ $currentSort === 'name' ? 'btn-primary' : 'btn-outline-secondary' }}">
                Firma adı @if($currentSort === 'name') <span class="material-symbols-outlined ms-1" style="font-size:1rem">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
            </a>
            <a href="{{ route('admin.companies.index', array_merge($q, ['sort' => 'created_at', 'direction' => ($currentSort === 'created_at' && $currentDirection === 'asc') ? 'desc' : 'asc'])) }}" class="btn btn-sm {{ $currentSort === 'created_at' ? 'btn-primary' : 'btn-outline-secondary' }}">
                Tarih @if($currentSort === 'created_at') <span class="material-symbols-outlined ms-1" style="font-size:1rem">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
            </a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <select id="companies-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="activate">Aktif yap</option>
                <option value="deactivate">Pasif yap</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="companies-bulk-apply">Uygula</button>
            <span class="small text-secondary"><span id="companies-selected-count">0</span> kayıt seçili</span>
        </div>
    </div>
    <form id="companies-bulk-form" action="{{ route('admin.companies.bulk') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="action" id="companies-bulk-action-input">
    </form>
    <div class="row g-4">
        @foreach($companies as $company)
        <div class="col-md-6 col-lg-4">
            <div class="border rounded-3xl p-4 h-100 transition-all hover:shadow-md {{ session('active_company_id') == $company->id ? 'border-primary bg-primary-50' : '' }}" style="border-color: var(--bs-primary-200);">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <input type="checkbox" class="form-check-input company-row-check" name="selected[]" value="{{ $company->id }}" form="companies-bulk-form" title="Seç">
                    <span class="invisible">.</span>
                </div>
                <div class="d-flex flex-column align-items-center text-center mb-3">
                    @php
                        $companyLogoUrl = $company->logo_url;
                    @endphp
                    @if($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}?v={{ time() }}" alt="{{ $company->name }}" class="rounded-3xl border mb-3" style="max-width: 100%; height: auto; max-height: 120px; object-fit: contain; border-color: var(--bs-primary-200) !important;">
                    @else
                    <div class="rounded-3xl border d-flex align-items-center justify-content-center bg-white text-secondary mb-3" style="width: 120px; height: 120px;">
                        <span class="material-symbols-outlined" style="font-size: 40px;">business</span>
                    </div>
                    @endif
                    <div class="w-100">
                        <h4 class="h6 fw-bold text-dark mb-1" style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; min-height: 2.5em;">{{ $company->name }}</h4>
                        @if($company->short_name && $company->short_name !== $company->name)
                        <p class="small text-secondary mb-0">{{ $company->short_name }}</p>
                        @endif
                    </div>
                </div>
                
                @if($company->tax_number)
                <div class="mb-2">
                    <p class="small text-secondary mb-0">Vergi No: <span class="fw-semibold text-dark">{{ $company->tax_number }}</span></p>
                </div>
                @endif
                
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge {{ $company->is_active ? 'bg-success-200 text-success' : 'bg-secondary-200 text-secondary' }} px-3 py-2 rounded-pill">
                        {{ $company->is_active ? 'Aktif' : 'Pasif' }}
                    </span>
                    @if(session('active_company_id') == $company->id)
                    <span class="badge bg-primary text-white px-3 py-2 rounded-pill">Aktif Firma</span>
                    @endif
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.companies.settings', $company) }}" class="btn btn-primary btn-sm flex-fill d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">settings</span>
                        Ayarlar
                    </a>
                    @if(session('active_company_id') != $company->id)
                    <form action="{{ route('admin.companies.switch') }}" method="POST" class="flex-fill">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company->id }}">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px;">swap_horiz</span>
                            Seç
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@if(!$companies->isEmpty())
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('companies-bulk-form');
    const actionSelect = document.getElementById('companies-bulk-action');
    const actionInput = document.getElementById('companies-bulk-action-input');
    const applyBtn = document.getElementById('companies-bulk-apply');
    const checkboxes = document.querySelectorAll('.company-row-check');
    const countEl = document.getElementById('companies-selected-count');
    function updateCount() { const n = document.querySelectorAll('.company-row-check:checked').length; countEl.textContent = n; }
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
    applyBtn.addEventListener('click', function () {
        const action = actionSelect.value;
        if (!action) return;
        const checked = document.querySelectorAll('.company-row-check:checked');
        if (checked.length === 0) { alert('Lütfen en az bir firma seçin.'); return; }
        checked.forEach(cb => form.appendChild(cb.cloneNode(true)));
        actionInput.value = action;
        form.submit();
    });
});
</script>
@endpush
@endif
@endsection
