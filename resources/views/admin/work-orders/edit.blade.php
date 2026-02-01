@extends('layouts.app')

@section('title', 'İş Emri Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">İş Emri Düzenle</h2>
        <p class="text-secondary mb-0">İş emri bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.work-orders.show', $workOrder->id) }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.work-orders.update', $workOrder->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="vehicle_id" class="form-label fw-semibold text-dark">Araç <span class="text-danger">*</span></label>
                <select name="vehicle_id" id="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                    <option value="">Seçiniz</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $workOrder->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="service_provider_id" class="form-label fw-semibold text-dark">Servis Sağlayıcı</label>
                <select name="service_provider_id" id="service_provider_id" class="form-select @error('service_provider_id') is-invalid @enderror">
                    <option value="">Seçiniz</option>
                    @foreach($serviceProviders as $provider)
                        <option value="{{ $provider->id }}" {{ old('service_provider_id', $workOrder->service_provider_id) == $provider->id ? 'selected' : '' }}>
                            {{ $provider->name }}
                        </option>
                    @endforeach
                </select>
                @error('service_provider_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="type" class="form-label fw-semibold text-dark">Tür <span class="text-danger">*</span></label>
                <input type="text" name="type" id="type" class="form-control @error('type') is-invalid @enderror" value="{{ old('type', $workOrder->type) }}" required>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="pending" {{ old('status', $workOrder->status) === 'pending' ? 'selected' : '' }}>Beklemede</option>
                    <option value="in_progress" {{ old('status', $workOrder->status) === 'in_progress' ? 'selected' : '' }}>Devam Ediyor</option>
                    <option value="completed" {{ old('status', $workOrder->status) === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="description" class="form-label fw-semibold text-dark">Açıklama</label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $workOrder->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.work-orders.show', $workOrder->id) }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
