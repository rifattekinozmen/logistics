@extends('layouts.app')

@section('title', 'Sevkiyat Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Sevkiyat Düzenle</h2>
        <p class="text-secondary mb-0">Sevkiyat bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.shipments.show', $shipment->id) }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.shipments.update', $shipment->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="order_id" class="form-label fw-semibold text-dark">Sipariş <span class="text-danger">*</span></label>
                <select name="order_id" id="order_id" class="form-select @error('order_id') is-invalid @enderror" required>
                    <option value="">Seçiniz</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}" {{ old('order_id', $shipment->order_id) == $order->id ? 'selected' : '' }}>
                            {{ $order->order_number }}
                        </option>
                    @endforeach
                </select>
                @error('order_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="vehicle_id" class="form-label fw-semibold text-dark">Araç <span class="text-danger">*</span></label>
                <select name="vehicle_id" id="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                    <option value="">Seçiniz</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $shipment->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="assigned" {{ old('status', $shipment->status) === 'assigned' ? 'selected' : '' }}>Atandı</option>
                    <option value="loaded" {{ old('status', $shipment->status) === 'loaded' ? 'selected' : '' }}>Yüklendi</option>
                    <option value="in_transit" {{ old('status', $shipment->status) === 'in_transit' ? 'selected' : '' }}>Yolda</option>
                    <option value="delivered" {{ old('status', $shipment->status) === 'delivered' ? 'selected' : '' }}>Teslim Edildi</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="notes" class="form-label fw-semibold text-dark">Notlar</label>
                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $shipment->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.shipments.show', $shipment->id) }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
