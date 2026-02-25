@extends('layouts.app')

@section('title', 'Müşteriler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">people</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Müşteriler</h2>
            <p class="text-secondary mb-0">Tüm müşterileri görüntüleyin ve yönetin</p>
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
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.customers.index', array_merge(request()->query(), ['export' => 'csv'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">table_chart</span>
                        CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.customers.index', array_merge(request()->query(), ['export' => 'xml'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">code</span>
                        XML
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-customers d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Müşteri
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="people" color="primary" col="col-md-4" />
    <x-index-stat-card title="Aktif" :value="$stats['active'] ?? 0" icon="check_circle" color="success" col="col-md-4" />
    <x-index-stat-card title="Pasif" :value="$stats['inactive'] ?? 0" icon="cancel" color="secondary" col="col-md-4" />
</div>

<div class="filter-area filter-area-customers rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-dark">Arama</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Müşteri adı ile ara..." class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-customers w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="customers-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="activate">Aktif yap</option>
                <option value="deactivate">Pasif yap</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="customers-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="customers-selected-count">0</span> kayıt seçili
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
                        <input type="checkbox" id="select-all-customers">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'name' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Müşteri Adı</span>
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
                            $direction = $currentSort === 'email' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>E-posta</span>
                            @if($currentSort === 'email')
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
                            $direction = $currentSort === 'phone' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'phone', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Telefon</span>
                            @if($currentSort === 'phone')
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
                        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'tax_number', 'direction' => $direction])) }}"
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
                            $direction = $currentSort === 'favorite_addresses_count' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'favorite_addresses_count', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Favori / Teslimat Adresleri</span>
                            @if($currentSort === 'favorite_addresses_count')
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
                        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}"
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
                @forelse($customers as $customer)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="customer-row-checkbox" value="{{ $customer->id }}">
                    </td>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $customer->name }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $customer->email ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $customer->phone ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $customer->tax_number ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        @if($customer->favorite_addresses_count > 0)
                            <a href="{{ route('admin.customers.show', $customer->id) }}#favorite-addresses" class="badge bg-primary-200 text-primary px-3 py-2 rounded-pill fw-semibold text-decoration-none">
                                <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">location_on</span>
                                {{ $customer->favorite_addresses_count }} adres
                            </a>
                        @else
                            <span class="text-secondary small">-</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        @if($customer->status == 1)
                            <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill fw-semibold">Aktif</span>
                        @else
                            <span class="badge bg-danger-200 text-danger px-3 py-2 rounded-pill fw-semibold">Pasif</span>
                        @endif
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline delete-form"
                                data-confirm="{{ $customer->name }}">
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
                    <td colspan="7" class="text-center p-0">
                        <x-empty-state
                            icon="group"
                            title="Henüz müşteri bulunmuyor"
                            message="Yeni bir müşteri ekleyerek başlayın."
                            actionText="İlk Müşteriyi Oluştur"
                            :actionUrl="route('admin.customers.create')"
                        />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="p-4 border-top">
        {{ $customers->links() }}
    </div>
    @endif
</div>

<form id="customers-bulk-form" method="POST" action="{{ route('admin.customers.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="customers-bulk-action-input">
</form>

@push('scripts')
<script>
document.querySelectorAll('.delete-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var f = form;
        showDeleteConfirm({
            name: f.dataset.confirm,
            onConfirm: function() { f.submit(); }
        });
    });
});

const customerMasterCheckbox = document.getElementById('select-all-customers');
const customerRowCheckboxes = document.querySelectorAll('.customer-row-checkbox');
const customerSelectedCountEl = document.getElementById('customers-selected-count');
const customerBulkApplyBtn = document.getElementById('customers-bulk-apply');
const customerBulkActionSelect = document.getElementById('customers-bulk-action');
const customerBulkForm = document.getElementById('customers-bulk-form');
const customerBulkActionInput = document.getElementById('customers-bulk-action-input');

function updateCustomerSelectedCount() {
    const selected = Array.from(customerRowCheckboxes).filter(cb => cb.checked);
    if (customerSelectedCountEl) {
        customerSelectedCountEl.textContent = selected.length.toString();
    }
    if (customerMasterCheckbox) {
        customerMasterCheckbox.checked = selected.length > 0 && selected.length === customerRowCheckboxes.length;
        customerMasterCheckbox.indeterminate = selected.length > 0 && selected.length < customerRowCheckboxes.length;
    }
}

if (customerMasterCheckbox) {
    customerMasterCheckbox.addEventListener('change', function () {
        const checked = customerMasterCheckbox.checked;
        customerRowCheckboxes.forEach(function (cb) {
            cb.checked = checked;
        });
        updateCustomerSelectedCount();
    });
}

customerRowCheckboxes.forEach(function (cb) {
    cb.addEventListener('change', updateCustomerSelectedCount);
});

if (customerBulkApplyBtn) {
    customerBulkApplyBtn.addEventListener('click', function () {
        const action = customerBulkActionSelect.value;
        const selected = Array.from(customerRowCheckboxes).filter(cb => cb.checked);

        if (! action) {
            alert('Lütfen bir toplu işlem seçin.');
            return;
        }

        if (selected.length === 0) {
            alert('Lütfen en az bir kayıt seçin.');
            return;
        }

        if (action === 'delete' && ! confirm('Seçili müşterileri silmek istediğinize emin misiniz?')) {
            return;
        }

        // Eski hidden input'ları temizle
        customerBulkForm.querySelectorAll('input[name=\"selected[]\"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected[]';
            hidden.value = cb.value;
            customerBulkForm.appendChild(hidden);
        });

        customerBulkActionInput.value = action;
        customerBulkForm.submit();
    });
}
</script>
@endpush
@endsection
