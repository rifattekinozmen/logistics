@extends('layouts.app')

@section('title', 'İş Ortakları (Business Partners) - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">handshake</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">İş Ortakları</h2>
            <p class="text-secondary mb-0">SAP Business Partner uyumlu müşteri ve tedarikçi yönetimi</p>
        </div>
    </div>
    <a href="{{ route('admin.business-partners.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni İş Ortağı
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="handshake" color="primary" col="col-md-6" />
    <x-index-stat-card title="Aktif" :value="$stats['active'] ?? 0" icon="check_circle" color="success" col="col-md-6" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.business-partners.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Tür</label>
            <select name="partner_type" class="form-select">
                <option value="">Tümü</option>
                <option value="customer" {{ request('partner_type') === 'customer' ? 'selected' : '' }}>Müşteri</option>
                <option value="vendor"   {{ request('partner_type') === 'vendor'   ? 'selected' : '' }}>Tedarikçi</option>
                <option value="carrier"  {{ request('partner_type') === 'carrier'  ? 'selected' : '' }}>Taşıyıcı</option>
                <option value="both"     {{ request('partner_type') === 'both'     ? 'selected' : '' }}>Müşteri & Tedarikçi</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-dark">Arama</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ad, BP no veya vergi no...">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
    </div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="business-partners-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="activate">Aktif yap</option>
                <option value="deactivate">Pasif yap</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="business-partners-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="business-partners-selected-count">0</span> kayıt seçili
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
                        <input type="checkbox" id="select-all-business-partners">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'partner_number' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.business-partners.index', array_merge(request()->query(), ['sort' => 'partner_number', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>BP No</span>
                            @if($currentSort === 'partner_number')
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
                            $direction = $currentSort === 'name' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.business-partners.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Ad</span>
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
                            $direction = $currentSort === 'partner_type' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.business-partners.index', array_merge(request()->query(), ['sort' => 'partner_type', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Tür</span>
                            @if($currentSort === 'partner_type')
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
                            $direction = $currentSort === 'tax_number' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.business-partners.index', array_merge(request()->query(), ['sort' => 'tax_number', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Vergi No</span>
                            @if($currentSort === 'tax_number')
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
                            $direction = $currentSort === 'currency' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.business-partners.index', array_merge(request()->query(), ['sort' => 'currency', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Para Birimi</span>
                            @if($currentSort === 'currency')
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
                        <a href="{{ route('admin.business-partners.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}"
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
                @forelse($partners as $partner)
                @php
                    $typeLabels = ['customer' => 'Müşteri', 'vendor' => 'Tedarikçi', 'carrier' => 'Taşıyıcı', 'both' => 'Müşteri & Tedarikçi'];
                    $typeColors = ['customer' => 'primary', 'vendor' => 'warning', 'carrier' => 'info', 'both' => 'success'];
                    $typeColor  = $typeColors[$partner->partner_type] ?? 'secondary';
                @endphp
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="business-partners-row-checkbox" value="{{ $partner->id }}">
                    </td>
                    <td class="align-middle"><span class="fw-bold font-monospace text-dark">{{ $partner->partner_number }}</span></td>
                    <td class="align-middle fw-semibold text-dark">{{ $partner->name }}</td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $typeColor }}-200 text-{{ $typeColor }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $typeLabels[$partner->partner_type] ?? $partner->partner_type }}
                        </span>
                    </td>
                    <td class="align-middle"><small class="text-secondary">{{ $partner->tax_number ?? '-' }}</small></td>
                    <td class="align-middle"><small class="text-secondary">{{ $partner->currency }}</small></td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $partner->status ? 'success' : 'danger' }}-200 text-{{ $partner->status ? 'success' : 'danger' }} px-3 py-2 rounded-pill">
                            {{ $partner->status ? 'Aktif' : 'Pasif' }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.business-partners.show', $partner->id) }}" class="btn btn-sm bg-info-200 text-info border-0" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.business-partners.edit', $partner->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.business-partners.destroy', $partner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu iş ortağını silmek istediğinize emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0" title="Sil">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <span class="material-symbols-outlined text-secondary d-block mb-2" style="font-size: 3rem;">handshake</span>
                        <p class="text-secondary mb-0">Henüz iş ortağı bulunmuyor.</p>
                        <a href="{{ route('admin.business-partners.create') }}" class="btn btn-primary btn-sm mt-2">İlk İş Ortağını Oluştur</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($partners->hasPages())
    <div class="p-4 border-top">{{ $partners->links() }}</div>
    @endif
</div>

<form id="business-partners-bulk-form" method="POST" action="{{ route('admin.business-partners.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="business-partners-bulk-action-input">
</form>
@endsection

@push('scripts')
<script>
const bpMaster = document.getElementById('select-all-business-partners');
const bpRows = document.querySelectorAll('.business-partners-row-checkbox');
const bpCountEl = document.getElementById('business-partners-selected-count');
const bpApplyBtn = document.getElementById('business-partners-bulk-apply');
const bpActionSelect = document.getElementById('business-partners-bulk-action');
const bpForm = document.getElementById('business-partners-bulk-form');
const bpActionInput = document.getElementById('business-partners-bulk-action-input');

function updateBusinessPartnersSelectedCount() {
    const selected = Array.from(bpRows).filter(cb => cb.checked);
    if (bpCountEl) {
        bpCountEl.textContent = selected.length.toString();
    }
    if (bpMaster) {
        bpMaster.checked = selected.length > 0 && selected.length === bpRows.length;
        bpMaster.indeterminate = selected.length > 0 && selected.length < bpRows.length;
    }
}

if (bpMaster) {
    bpMaster.addEventListener('change', function () {
        const checked = bpMaster.checked;
        bpRows.forEach(function (cb) {
            cb.checked = checked;
        });
        updateBusinessPartnersSelectedCount();
    });
}

bpRows.forEach(function (cb) {
    cb.addEventListener('change', updateBusinessPartnersSelectedCount);
});

if (bpApplyBtn) {
    bpApplyBtn.addEventListener('click', function () {
        const action = bpActionSelect.value;
        const selected = Array.from(bpRows).filter(cb => cb.checked);

        if (! action) {
            alert('Lütfen bir toplu işlem seçin.');
            return;
        }

        if (selected.length === 0) {
            alert('Lütfen en az bir kayıt seçin.');
            return;
        }

        if (action === 'delete' && ! confirm('Seçili iş ortaklarını silmek istediğinize emin misiniz?')) {
            return;
        }

        bpForm.querySelectorAll('input[name="selected[]"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected[]';
            hidden.value = cb.value;
            bpForm.appendChild(hidden);
        });

        bpActionInput.value = action;
        bpForm.submit();
    });
}
</script>
@endpush
