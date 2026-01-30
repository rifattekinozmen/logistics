@extends('layouts.app')

@section('title', 'Araç Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Araç Detayı</h2>
        <p class="text-secondary mb-0">Plaka: {{ $vehicle->plate }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-light">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Genel Bilgiler</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Plaka</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->plate }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Durum</label>
                    <div>
                        @php
                            $statusColors = [0 => 'secondary', 1 => 'success', 2 => 'warning'];
                            $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'Bakımda'];
                            $color = $statusColors[$vehicle->status] ?? 'secondary';
                            $label = $statusLabels[$vehicle->status] ?? '-';
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2 rounded-pill">
                            {{ $label }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Marka</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->brand }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Model</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->model }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Yıl</label>
                    <p class="text-dark mb-0">{{ $vehicle->year ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Araç Tipi</label>
                    <p class="text-dark mb-0">
                        @php
                            $typeLabels = [
                                'truck' => 'Kamyon',
                                'van' => 'Minibüs',
                                'car' => 'Araba',
                                'trailer' => 'Römork',
                            ];
                        @endphp
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                            {{ $typeLabels[$vehicle->vehicle_type] ?? $vehicle->vehicle_type }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Kapasite Bilgileri</h3>
            <div class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label small fw-semibold text-secondary">Kapasite (kg)</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->capacity_kg ? number_format($vehicle->capacity_kg, 2).' kg' : '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Kapasite (m³)</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->capacity_m3 ? number_format($vehicle->capacity_m3, 2).' m³' : '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Şube</label>
                    <p class="fw-bold text-dark mb-0">{{ $vehicle->branch->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Hızlı İşlemler</h3>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    Düzenle
                </a>
                <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('Bu aracı silmek istediğinize emin misiniz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">delete</span>
                        Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
