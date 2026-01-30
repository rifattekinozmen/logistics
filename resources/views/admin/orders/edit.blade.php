@extends('layouts.app')

@section('title', 'Sipariş Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Sipariş Düzenle</h2>
        <p class="text-secondary mb-0">Sipariş bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Sipariş No</label>
                <input type="text" value="{{ $order->order_number }}" class="form-control" disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="pending" {{ old('status', $order->status) === 'pending' ? 'selected' : '' }}>Beklemede</option>
                    <option value="assigned" {{ old('status', $order->status) === 'assigned' ? 'selected' : '' }}>Atandı</option>
                    <option value="in_transit" {{ old('status', $order->status) === 'in_transit' ? 'selected' : '' }}>Yolda</option>
                    <option value="delivered" {{ old('status', $order->status) === 'delivered' ? 'selected' : '' }}>Teslim Edildi</option>
                    <option value="cancelled" {{ old('status', $order->status) === 'cancelled' ? 'selected' : '' }}>İptal</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Alış Adresi <span class="text-danger">*</span></label>
                <textarea name="pickup_address" class="form-control @error('pickup_address') is-invalid @enderror" rows="2" required>{{ old('pickup_address', $order->pickup_address) }}</textarea>
                @error('pickup_address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Teslimat Adresi <span class="text-danger">*</span></label>
                <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" rows="2" required>{{ old('delivery_address', $order->delivery_address) }}</textarea>
                @error('delivery_address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Planlanan Alış Tarihi</label>
                <input type="datetime-local" name="planned_pickup_date" value="{{ old('planned_pickup_date', $order->planned_pickup_date ? $order->planned_pickup_date->format('Y-m-d\TH:i') : '') }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Planlanan Teslimat Tarihi</label>
                <input type="datetime-local" name="planned_delivery_date" value="{{ old('planned_delivery_date', $order->planned_delivery_date ? $order->planned_delivery_date->format('Y-m-d\TH:i') : '') }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Toplam Ağırlık (kg)</label>
                <input type="number" step="0.01" name="total_weight" value="{{ old('total_weight', $order->total_weight) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Toplam Hacim (m³)</label>
                <input type="number" step="0.01" name="total_volume" value="{{ old('total_volume', $order->total_volume) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="is_dangerous" value="1" id="is_dangerous" {{ old('is_dangerous', $order->is_dangerous) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_dangerous">
                        Tehlikeli Madde
                    </label>
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Notlar</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $order->notes) }}</textarea>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
