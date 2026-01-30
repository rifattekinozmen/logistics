@extends('layouts.app')

@section('title', 'Yeni Sipariş - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Sipariş Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir sipariş kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-warning-200);">
    <form action="{{ route('admin.orders.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Müşteri <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select border-warning-200 focus:border-warning focus:ring-warning @error('customer_id') is-invalid border-danger @enderror" required>
                    <option value="">Müşteri Seçin</option>
                    @foreach($customers ?? [] as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                    @endforeach
                </select>
                @error('customer_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum</label>
                <select name="status" class="form-select border-warning-200 focus:border-warning focus:ring-warning">
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                    <option value="assigned" {{ old('status') === 'assigned' ? 'selected' : '' }}>Atandı</option>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Alış Adresi <span class="text-danger">*</span></label>
                <textarea name="pickup_address" class="form-control border-warning-200 focus:border-warning focus:ring-warning @error('pickup_address') is-invalid border-danger @enderror" rows="2" required>{{ old('pickup_address') }}</textarea>
                @error('pickup_address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Teslimat Adresi <span class="text-danger">*</span></label>
                <textarea name="delivery_address" class="form-control border-warning-200 focus:border-warning focus:ring-warning @error('delivery_address') is-invalid border-danger @enderror" rows="2" required>{{ old('delivery_address') }}</textarea>
                @error('delivery_address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Planlanan Alış Tarihi</label>
                <input type="datetime-local" name="planned_pickup_date" value="{{ old('planned_pickup_date') }}" class="form-control border-warning-200 focus:border-warning focus:ring-warning">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Planlanan Teslimat Tarihi</label>
                <input type="datetime-local" name="planned_delivery_date" value="{{ old('planned_delivery_date') }}" class="form-control border-warning-200 focus:border-warning focus:ring-warning">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Toplam Ağırlık (kg)</label>
                <input type="number" step="0.01" name="total_weight" value="{{ old('total_weight') }}" class="form-control border-warning-200 focus:border-warning focus:ring-warning">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Toplam Hacim (m³)</label>
                <input type="number" step="0.01" name="total_volume" value="{{ old('total_volume') }}" class="form-control border-warning-200 focus:border-warning focus:ring-warning">
            </div>

            <div class="col-md-6">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="is_dangerous" value="1" id="is_dangerous" {{ old('is_dangerous') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_dangerous">
                        Tehlikeli Madde
                    </label>
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Notlar</label>
                <textarea name="notes" class="form-control border-warning-200 focus:border-warning focus:ring-warning" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-warning-200);">
            <a href="{{ route('admin.orders.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Sipariş Oluştur</button>
        </div>
    </form>
</div>
@endsection
