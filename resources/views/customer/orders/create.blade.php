@extends('layouts.customer-app')

@section('title', 'Yeni Sipariş - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">add_shopping_cart</span>
            <h2 class="h3 fw-bold text-dark mb-0">Yeni Sipariş</h2>
        </div>
        <p class="text-secondary mb-0">Yeni sipariş oluşturun</p>
    </div>
    <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <form action="{{ route('customer.orders.store') }}" method="POST" id="orderForm">
                @csrf

                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="pickup_address" class="form-label fw-semibold text-dark">Alış Adresi <span class="text-danger">*</span></label>
                        <select id="pickup_address_select" class="form-select mb-2">
                            <option value="">— Kayıtlı adreslerden seç veya manuel gir —</option>
                            @foreach($pickupAddresses ?? [] as $addr)
                            <option value="{{ $addr->address }}">{{ $addr->name }}</option>
                            @endforeach
                        </select>
                        <textarea name="pickup_address"
                                  id="pickup_address"
                                  class="form-control @error('pickup_address') is-invalid @enderror"
                                  rows="2"
                                  required
                                  placeholder="Adresi buraya yazın veya yukarıdan seçin">{{ old('pickup_address') }}</textarea>
                        @error('pickup_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="delivery_address" class="form-label fw-semibold text-dark">Teslimat Adresi <span class="text-danger">*</span></label>
                        <select id="delivery_address_select" class="form-select mb-2">
                            <option value="">— Kayıtlı adreslerden seç veya manuel gir —</option>
                            @foreach($deliveryAddresses ?? [] as $addr)
                            <option value="{{ $addr->address }}">{{ $addr->name }}</option>
                            @endforeach
                        </select>
                        <textarea name="delivery_address"
                                  id="delivery_address"
                                  class="form-control @error('delivery_address') is-invalid @enderror"
                                  rows="2"
                                  required
                                  placeholder="Adresi buraya yazın veya yukarıdan seçin">{{ old('delivery_address') }}</textarea>
                        @error('delivery_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="planned_delivery_date" class="form-label fw-semibold text-dark">Planlanan Teslimat Tarihi</label>
                        <input type="date"
                               name="planned_delivery_date"
                               id="planned_delivery_date"
                               class="form-control @error('planned_delivery_date') is-invalid @enderror"
                               value="{{ old('planned_delivery_date') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               required>
                        @error('planned_delivery_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="total_weight" class="form-label fw-semibold text-dark">Ağırlık (kg)</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="total_weight"
                               id="total_weight"
                               class="form-control @error('total_weight') is-invalid @enderror"
                               value="{{ old('total_weight') }}">
                        @error('total_weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="total_volume" class="form-label fw-semibold text-dark">Hacim (m³)</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="total_volume"
                               id="total_volume"
                               class="form-control @error('total_volume') is-invalid @enderror"
                               value="{{ old('total_volume') }}">
                        @error('total_volume')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox"
                                   name="is_dangerous"
                                   id="is_dangerous"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('is_dangerous') ? 'checked' : '' }}>
                            <label for="is_dangerous" class="form-check-label fw-semibold text-dark">
                                Tehlikeli Madde
                            </label>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label for="notes" class="form-label fw-semibold text-dark">Notlar</label>
                        <textarea name="notes"
                                  id="notes"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">close</span>
                        İptal
                    </a>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">check</span>
                        Sipariş Oluştur
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 sticky-top" style="top: 1rem;">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">summarize</span>
                Sipariş Özeti
            </h5>
            <div class="order-summary-content">
                <div class="mb-3">
                    <span class="text-secondary small d-block">Alış Adresi</span>
                    <span id="summary_pickup" class="small text-break">—</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Teslimat Adresi</span>
                    <span id="summary_delivery" class="small text-break">—</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Planlanan Teslimat</span>
                    <span id="summary_delivery_date">—</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Ağırlık / Hacim</span>
                    <span id="summary_weight_volume">—</span>
                </div>
                <div>
                    <span class="text-secondary small d-block">Tehlikeli Madde</span>
                    <span id="summary_dangerous">Hayır</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pickupSelect = document.getElementById('pickup_address_select');
    const deliverySelect = document.getElementById('delivery_address_select');
    const pickupTextarea = document.getElementById('pickup_address');
    const deliveryTextarea = document.getElementById('delivery_address');

    const summary = {
        pickup: document.getElementById('summary_pickup'),
        delivery: document.getElementById('summary_delivery'),
        deliveryDate: document.getElementById('summary_delivery_date'),
        weightVolume: document.getElementById('summary_weight_volume'),
        dangerous: document.getElementById('summary_dangerous'),
    };

    function updateSummary() {
        summary.pickup.textContent = pickupTextarea.value || '—';
        summary.delivery.textContent = deliveryTextarea.value || '—';
        const dd = document.getElementById('planned_delivery_date').value;
        summary.deliveryDate.textContent = dd ? dd.split('-').reverse().join('.') : '—';
        const w = document.getElementById('total_weight').value;
        const v = document.getElementById('total_volume').value;
        summary.weightVolume.textContent = [w, v].filter(Boolean).length ? `${w || '—'} kg / ${v || '—'} m³` : '—';
        summary.dangerous.textContent = document.getElementById('is_dangerous').checked ? 'Evet' : 'Hayır';
    }

    pickupSelect.addEventListener('change', function () {
        if (this.value) pickupTextarea.value = this.value;
        updateSummary();
    });
    deliverySelect.addEventListener('change', function () {
        if (this.value) deliveryTextarea.value = this.value;
        updateSummary();
    });

    ['pickup_address', 'delivery_address', 'planned_delivery_date', 'total_weight', 'total_volume', 'is_dangerous'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateSummary);
        if (el) el.addEventListener('change', updateSummary);
    });

    updateSummary();
});
</script>
@endsection
