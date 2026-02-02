@extends('layouts.app')

@section('title', 'Müşteriler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Müşteriler</h2>
        <p class="text-secondary mb-0">Tüm müşterileri görüntüleyin ve yönetin</p>
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

<div class="filter-area filter-area-customers rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-3">
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
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Müşteri Adı</th>
                    <th class="border-0 fw-semibold text-secondary small">E-posta</th>
                    <th class="border-0 fw-semibold text-secondary small">Telefon</th>
                    <th class="border-0 fw-semibold text-secondary small">Vergi No</th>
                    <th class="border-0 fw-semibold text-secondary small">Favori / Teslimat Adresleri</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
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
                            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?');">
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
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">people</span>
                            <p class="text-secondary mb-0">Henüz müşteri bulunmuyor.</p>
                            <a href="{{ route('admin.customers.create') }}" class="btn btn-customers btn-sm mt-2">İlk Müşteriyi Oluştur</a>
                        </div>
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
@endsection
