@extends('layouts.app')

@section('title', 'Araç Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Araç Düzenle</h2>
        <p class="text-secondary mb-0">Araç bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.vehicles.update', $vehicle->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Plaka <span class="text-danger">*</span></label>
                <input type="text" name="plate" value="{{ old('plate', $vehicle->plate) }}" class="form-control @error('plate') is-invalid @enderror" required>
                @error('plate')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="1" {{ old('status', $vehicle->status) == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status', $vehicle->status) == 0 ? 'selected' : '' }}>Pasif</option>
                    <option value="2" {{ old('status', $vehicle->status) == 2 ? 'selected' : '' }}>Bakımda</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Marka <span class="text-danger">*</span></label>
                <input type="text" name="brand" value="{{ old('brand', $vehicle->brand) }}" class="form-control @error('brand') is-invalid @enderror" required>
                @error('brand')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                <input type="text" name="model" value="{{ old('model', $vehicle->model) }}" class="form-control @error('model') is-invalid @enderror" required>
                @error('model')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Yıl</label>
                <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" min="1900" max="{{ date('Y') }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Araç Tipi <span class="text-danger">*</span></label>
                <select name="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror" required>
                    <option value="truck" {{ old('vehicle_type', $vehicle->vehicle_type) === 'truck' ? 'selected' : '' }}>Kamyon</option>
                    <option value="van" {{ old('vehicle_type', $vehicle->vehicle_type) === 'van' ? 'selected' : '' }}>Minibüs</option>
                    <option value="car" {{ old('vehicle_type', $vehicle->vehicle_type) === 'car' ? 'selected' : '' }}>Araba</option>
                    <option value="trailer" {{ old('vehicle_type', $vehicle->vehicle_type) === 'trailer' ? 'selected' : '' }}>Römork</option>
                </select>
                @error('vehicle_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Kapasite (kg)</label>
                <input type="number" step="0.01" name="capacity_kg" value="{{ old('capacity_kg', $vehicle->capacity_kg) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Kapasite (m³)</label>
                <input type="number" step="0.01" name="capacity_m3" value="{{ old('capacity_m3', $vehicle->capacity_m3) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Şube</label>
                <select name="branch_id" class="form-select">
                    <option value="">Şube Seçin</option>
                    @foreach($branches ?? [] as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $vehicle->branch_id) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top">
            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-light">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
