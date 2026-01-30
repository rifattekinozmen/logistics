@extends('layouts.app')

@section('title', 'Yeni İş Emri - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni İş Emri Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir iş emri kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.work-orders.index') }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-warning-200);">
    <form action="{{ route('admin.work-orders.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Araç <span class="text-danger">*</span></label>
                <select name="vehicle_id" class="form-select border-warning-200 focus:border-warning focus:ring-warning @error('vehicle_id') is-invalid border-danger @enderror" required>
                    <option value="">Araç Seçin</option>
                    @foreach($vehicles ?? [] as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                    </option>
                    @endforeach
                </select>
                @error('vehicle_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Servis Sağlayıcı</label>
                <select name="service_provider_id" class="form-select border-warning-200 focus:border-warning focus:ring-warning @error('service_provider_id') is-invalid border-danger @enderror">
                    <option value="">Servis Sağlayıcı Seçin</option>
                    @foreach($serviceProviders ?? [] as $provider)
                    <option value="{{ $provider->id }}" {{ old('service_provider_id') == $provider->id ? 'selected' : '' }}>
                        {{ $provider->name }}
                    </option>
                    @endforeach
                </select>
                @error('service_provider_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Tür <span class="text-danger">*</span></label>
                <select name="type" class="form-select border-warning-200 focus:border-warning focus:ring-warning @error('type') is-invalid border-danger @enderror" required>
                    <option value="">Tür Seçin</option>
                    <option value="maintenance" {{ old('type') === 'maintenance' ? 'selected' : '' }}>Bakım</option>
                    <option value="repair" {{ old('type') === 'repair' ? 'selected' : '' }}>Tamir</option>
                    <option value="inspection" {{ old('type') === 'inspection' ? 'selected' : '' }}>Muayene</option>
                </select>
                @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-warning-200 focus:border-warning focus:ring-warning @error('status') is-invalid border-danger @enderror" required>
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Onay Bekliyor</option>
                    <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Onaylandı</option>
                    <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>Devam Ediyor</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Açıklama</label>
                <textarea name="description" class="form-control border-warning-200 focus:border-warning focus:ring-warning @error('description') is-invalid border-danger @enderror" rows="4">{{ old('description') }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-warning-200);">
            <a href="{{ route('admin.work-orders.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">İş Emri Oluştur</button>
        </div>
    </form>
</div>
@endsection
