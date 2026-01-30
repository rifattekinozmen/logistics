@extends('layouts.app')

@section('title', 'Yeni Araç - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Araç Ekle</h2>
        <p class="text-secondary mb-0">Yeni bir araç kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-info-200);">
    <form action="{{ route('admin.vehicles.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Plaka <span class="text-danger">*</span></label>
                <input type="text" name="plate" value="{{ old('plate') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('plate') is-invalid border-danger @enderror" required>
                @error('plate')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-info-200 focus:border-info focus:ring-info @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Pasif</option>
                    <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>Bakımda</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Marka <span class="text-danger">*</span></label>
                <input type="text" name="brand" value="{{ old('brand') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('brand') is-invalid border-danger @enderror" required>
                @error('brand')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Model <span class="text-danger">*</span></label>
                <input type="text" name="model" value="{{ old('model') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('model') is-invalid border-danger @enderror" required>
                @error('model')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Yıl</label>
                <input type="number" name="year" value="{{ old('year') }}" min="1900" max="{{ date('Y') }}" class="form-control border-info-200 focus:border-info focus:ring-info">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Araç Tipi <span class="text-danger">*</span></label>
                <select name="vehicle_type" class="form-select border-info-200 focus:border-info focus:ring-info @error('vehicle_type') is-invalid border-danger @enderror" required>
                    <option value="truck" {{ old('vehicle_type') === 'truck' ? 'selected' : '' }}>Kamyon</option>
                    <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>Minibüs</option>
                    <option value="car" {{ old('vehicle_type') === 'car' ? 'selected' : '' }}>Araba</option>
                    <option value="trailer" {{ old('vehicle_type') === 'trailer' ? 'selected' : '' }}>Römork</option>
                </select>
                @error('vehicle_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Kapasite (kg)</label>
                <input type="number" step="0.01" name="capacity_kg" value="{{ old('capacity_kg') }}" class="form-control border-info-200 focus:border-info focus:ring-info">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Kapasite (m³)</label>
                <input type="number" step="0.01" name="capacity_m3" value="{{ old('capacity_m3') }}" class="form-control border-info-200 focus:border-info focus:ring-info">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Şube</label>
                <select name="branch_id" class="form-select border-info-200 focus:border-info focus:ring-info">
                    <option value="">Şube Seçin</option>
                    @foreach($branches ?? [] as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-info-200);">
            <a href="{{ route('admin.vehicles.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Araç Ekle</button>
        </div>
    </form>
</div>
@endsection
