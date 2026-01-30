@extends('layouts.app')

@section('title', 'Yeni Sevkiyat - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Sevkiyat Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir sevkiyat kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.shipments.index') }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <form action="{{ route('admin.shipments.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Sipariş <span class="text-danger">*</span></label>
                <select name="order_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('order_id') is-invalid border-danger @enderror" required>
                    <option value="">Sipariş Seçin</option>
                    @foreach($orders ?? [] as $order)
                    <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                        #{{ $order->order_number }} - {{ $order->customer->name ?? '' }}
                    </option>
                    @endforeach
                </select>
                @error('order_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Araç <span class="text-danger">*</span></label>
                <select name="vehicle_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('vehicle_id') is-invalid border-danger @enderror" required>
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
                <label class="form-label fw-semibold text-dark">Şoför</label>
                <select name="driver_id" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('driver_id') is-invalid border-danger @enderror">
                    <option value="">Şoför Seçin</option>
                </select>
                @error('driver_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('status') is-invalid border-danger @enderror" required>
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                    <option value="in_transit" {{ old('status') === 'in_transit' ? 'selected' : '' }}>Yolda</option>
                    <option value="delivered" {{ old('status') === 'delivered' ? 'selected' : '' }}>Teslim Edildi</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Planlanan Alış Tarihi <span class="text-danger">*</span></label>
                <input type="datetime-local" name="planned_pickup_date" value="{{ old('planned_pickup_date') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('planned_pickup_date') is-invalid border-danger @enderror" required>
                @error('planned_pickup_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Planlanan Teslimat Tarihi</label>
                <input type="datetime-local" name="planned_delivery_date" value="{{ old('planned_delivery_date') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('planned_delivery_date') is-invalid border-danger @enderror">
                @error('planned_delivery_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
            <a href="{{ route('admin.shipments.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Sevkiyat Oluştur</button>
        </div>
    </form>
</div>
@endsection
