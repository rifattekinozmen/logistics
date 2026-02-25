@extends('layouts.app')

@section('title', 'Fiyatlandırma Koşulları - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">price_check</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Fiyatlandırma Koşulları</h2>
            <p class="text-secondary mb-0">SAP uyumlu navlun fiyat hesaplama koşulları</p>
        </div>
    </div>
    <a href="{{ route('admin.pricing-conditions.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Koşul
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="price_check" color="primary" col="col-md-6" />
    <x-index-stat-card title="Aktif" :value="$stats['active'] ?? 0" icon="check_circle" color="success" col="col-md-6" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.pricing-conditions.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Koşul Türü</label>
            <select name="condition_type" class="form-select">
                <option value="">Tümü</option>
                <option value="weight_based"   {{ request('condition_type') === 'weight_based'   ? 'selected' : '' }}>Ağırlık Bazlı</option>
                <option value="distance_based" {{ request('condition_type') === 'distance_based' ? 'selected' : '' }}>Mesafe Bazlı</option>
                <option value="flat"           {{ request('condition_type') === 'flat'           ? 'selected' : '' }}>Sabit Ücret</option>
                <option value="zone_based"     {{ request('condition_type') === 'zone_based'     ? 'selected' : '' }}>Bölge Bazlı</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
    </div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="pricing-conditions-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="activate">Aktif yap</option>
                <option value="deactivate">Pasif yap</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="pricing-conditions-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="pricing-conditions-selected-count">0</span> kayıt seçili
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
                        <input type="checkbox" id="select-all-pricing-conditions">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'name' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.pricing-conditions.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Koşul Adı</span>
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
                            $direction = $currentSort === 'condition_type' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.pricing-conditions.index', array_merge(request()->query(), ['sort' => 'condition_type', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Tür</span>
                            @if($currentSort === 'condition_type')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">Güzergah</th>
                    <th class="border-0 fw-semibold text-secondary small">Fiyat</th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'currency' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.pricing-conditions.index', array_merge(request()->query(), ['sort' => 'currency', 'direction' => $direction])) }}"
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
                            $direction = $currentSort === 'valid_from' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.pricing-conditions.index', array_merge(request()->query(), ['sort' => 'valid_from', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Geçerlilik</span>
                            @if($currentSort === 'valid_from')
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
                        <a href="{{ route('admin.pricing-conditions.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}"
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
                @forelse($conditions as $condition)
                @php
                    $typeLabels = ['weight_based' => 'Ağırlık', 'distance_based' => 'Mesafe', 'flat' => 'Sabit', 'zone_based' => 'Bölge'];
                    $typeColors = ['weight_based' => 'info', 'distance_based' => 'primary', 'flat' => 'success', 'zone_based' => 'warning'];
                    $typeColor  = $typeColors[$condition->condition_type] ?? 'secondary';

                    $priceText = match($condition->condition_type) {
                        'weight_based'   => number_format($condition->price_per_kg, 2).'/'.'kg',
                        'distance_based' => number_format($condition->price_per_km, 2).'/'.'km',
                        'flat','zone_based' => number_format($condition->flat_rate, 2),
                        default => '-',
                    };
                @endphp
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="pricing-conditions-row-checkbox" value="{{ $condition->id }}">
                    </td>
                    <td class="align-middle fw-semibold text-dark">{{ $condition->name }}</td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $typeColor }}-200 text-{{ $typeColor }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $typeLabels[$condition->condition_type] ?? $condition->condition_type }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $condition->route_origin ?? '*' }} → {{ $condition->route_destination ?? '*' }}
                        </small>
                    </td>
                    <td class="align-middle"><span class="fw-semibold">{{ $priceText }}</span></td>
                    <td class="align-middle"><small class="text-secondary">{{ $condition->currency }}</small></td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $condition->valid_from ? $condition->valid_from->format('d.m.Y') : '∞' }}
                            —
                            {{ $condition->valid_to ? $condition->valid_to->format('d.m.Y') : '∞' }}
                        </small>
                    </td>
                    <td class="align-middle">
                        <span class="badge bg-{{ $condition->status ? 'success' : 'danger' }}-200 text-{{ $condition->status ? 'success' : 'danger' }} px-3 py-2 rounded-pill">
                            {{ $condition->status ? 'Aktif' : 'Pasif' }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.pricing-conditions.edit', $condition->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.pricing-conditions.destroy', $condition->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu fiyatlandırma koşulunu silmek istediğinize emin misiniz?')">
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
                    <td colspan="8" class="text-center py-5">
                        <span class="material-symbols-outlined text-secondary d-block mb-2" style="font-size: 3rem;">price_change</span>
                        <p class="text-secondary mb-0">Henüz fiyatlandırma koşulu bulunmuyor.</p>
                        <a href="{{ route('admin.pricing-conditions.create') }}" class="btn btn-primary btn-sm mt-2">İlk Koşulu Oluştur</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($conditions->hasPages())
    <div class="p-4 border-top">{{ $conditions->links() }}</div>
    @endif
</div>

<form id="pricing-conditions-bulk-form" method="POST" action="{{ route('admin.pricing-conditions.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="pricing-conditions-bulk-action-input">
</form>
@endsection

@push('scripts')
<script>
const pcMaster = document.getElementById('select-all-pricing-conditions');
const pcRows = document.querySelectorAll('.pricing-conditions-row-checkbox');
const pcCountEl = document.getElementById('pricing-conditions-selected-count');
const pcApplyBtn = document.getElementById('pricing-conditions-bulk-apply');
const pcActionSelect = document.getElementById('pricing-conditions-bulk-action');
const pcForm = document.getElementById('pricing-conditions-bulk-form');
const pcActionInput = document.getElementById('pricing-conditions-bulk-action-input');

function updatePricingConditionsSelectedCount() {
    const selected = Array.from(pcRows).filter(cb => cb.checked);
    if (pcCountEl) {
        pcCountEl.textContent = selected.length.toString();
    }
    if (pcMaster) {
        pcMaster.checked = selected.length > 0 && selected.length === pcRows.length;
        pcMaster.indeterminate = selected.length > 0 && selected.length < pcRows.length;
    }
}

if (pcMaster) {
    pcMaster.addEventListener('change', function () {
        const checked = pcMaster.checked;
        pcRows.forEach(function (cb) {
            cb.checked = checked;
        });
        updatePricingConditionsSelectedCount();
    });
}

pcRows.forEach(function (cb) {
    cb.addEventListener('change', updatePricingConditionsSelectedCount);
});

if (pcApplyBtn) {
    pcApplyBtn.addEventListener('click', function () {
        const action = pcActionSelect.value;
        const selected = Array.from(pcRows).filter(cb => cb.checked);

        if (! action) {
            alert('Lütfen bir toplu işlem seçin.');
            return;
        }

        if (selected.length === 0) {
            alert('Lütfen en az bir kayıt seçin.');
            return;
        }

        if (action === 'delete' && ! confirm('Seçili fiyatlandırma koşullarını silmek istediğinize emin misiniz?')) {
            return;
        }

        pcForm.querySelectorAll('input[name="selected[]"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected[]';
            hidden.value = cb.value;
            pcForm.appendChild(hidden);
        });

        pcActionInput.value = action;
        pcForm.submit();
    });
}
</script>
@endpush
