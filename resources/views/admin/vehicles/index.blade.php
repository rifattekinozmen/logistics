@extends('layouts.app')

@section('title', 'Araçlar - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Araçlar</h2>
        <p class="text-secondary mb-0">Tüm araçları görüntüleyin ve yönetin</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                Dışa Aktar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.vehicles.index', array_merge(request()->query(), ['export' => 'csv'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">table_chart</span>
                        CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.vehicles.index', array_merge(request()->query(), ['export' => 'xml'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">code</span>
                        XML
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-vehicles d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Araç
        </a>
    </div>
</div>

<div class="filter-area filter-area-vehicles rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.vehicles.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pasif</option>
                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Bakımda</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Araç Tipi</label>
            <select name="vehicle_type" class="form-select">
                <option value="">Tümü</option>
                <option value="truck" {{ request('vehicle_type') === 'truck' ? 'selected' : '' }}>Kamyon</option>
                <option value="van" {{ request('vehicle_type') === 'van' ? 'selected' : '' }}>Minibüs</option>
                <option value="car" {{ request('vehicle_type') === 'car' ? 'selected' : '' }}>Araba</option>
                <option value="trailer" {{ request('vehicle_type') === 'trailer' ? 'selected' : '' }}>Römork</option>
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
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-vehicles w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Plaka</th>
                    <th class="border-0 fw-semibold text-secondary small">Marka/Model</th>
                    <th class="border-0 fw-semibold text-secondary small">Tip</th>
                    <th class="border-0 fw-semibold text-secondary small">Yıl</th>
                    <th class="border-0 fw-semibold text-secondary small">Kapasite</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $vehicle->plate }}</span>
                    </td>
                    <td class="align-middle">
                        {{ $vehicle->brand }} {{ $vehicle->model }}
                    </td>
                    <td class="align-middle">
                        @php
                            $typeLabels = [
                                'truck' => 'Kamyon',
                                'van' => 'Minibüs',
                                'car' => 'Araba',
                                'trailer' => 'Römork',
                            ];
                        @endphp
                        <span class="badge bg-secondary-200 text-secondary px-3 py-2 rounded-pill fw-semibold">
                            {{ $typeLabels[$vehicle->vehicle_type] ?? $vehicle->vehicle_type }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $vehicle->year ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            @if($vehicle->capacity_kg)
                                {{ number_format($vehicle->capacity_kg, 0) }} kg
                            @else
                                -
                            @endif
                        </small>
                    </td>
                    <td class="align-middle">
                        @php
                            $statusColors = [0 => 'secondary', 1 => 'success', 2 => 'warning'];
                            $softColors = ['secondary' => 'secondary-200', 'success' => 'success-200', 'warning' => 'warning-200'];
                            $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'Bakımda'];
                            $color = $statusColors[$vehicle->status] ?? 'secondary';
                            $softColor = $softColors[$color] ?? 'secondary-200';
                            $label = $statusLabels[$vehicle->status] ?? '-';
                        @endphp
                        <span class="badge bg-{{ $softColor }} text-{{ $color }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu aracı silmek istediğinize emin misiniz?');">
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
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">local_shipping</span>
                            <p class="text-secondary mb-0">Henüz araç bulunmuyor.</p>
                            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-vehicles btn-sm mt-2">İlk Aracı Ekle</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vehicles->hasPages())
    <div class="p-4 border-top">
        {{ $vehicles->links() }}
    </div>
    @endif
</div>
@endsection
