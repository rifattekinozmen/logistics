@extends('layouts.app')

@section('title', 'Yeni Sipariş - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Sipariş Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir sipariş kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-warning-200);">
            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <strong>Lütfen aşağıdaki hataları düzeltin:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Müşteri <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-select border-warning-200 focus:border-warning focus:ring-warning @error('customer_id') is-invalid border-danger @enderror" required>
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
                        <select id="pickup_address_select" class="form-select border-warning-200 focus:border-warning focus:ring-warning mb-2">
                            <option value="">— Kayıtlı adreslerden seç veya manuel gir —</option>
                        </select>
                        <textarea name="pickup_address" id="pickup_address" class="form-control border-warning-200 focus:border-warning focus:ring-warning @error('pickup_address') is-invalid border-danger @enderror" rows="2" required placeholder="Adresi buraya yazın veya yukarıdan seçin">{{ old('pickup_address') }}</textarea>
                        @error('pickup_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold text-dark">Teslimat Adresi <span class="text-danger">*</span></label>
                        <select id="delivery_address_select" class="form-select border-warning-200 focus:border-warning focus:ring-warning mb-2">
                            <option value="">— Kayıtlı adreslerden seç veya manuel gir —</option>
                        </select>
                        <textarea name="delivery_address" id="delivery_address" class="form-control border-warning-200 focus:border-warning focus:ring-warning @error('delivery_address') is-invalid border-danger @enderror" rows="2" required placeholder="Adresi buraya yazın veya yukarıdan seçin">{{ old('delivery_address') }}</textarea>
                        @error('delivery_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Planlanan Alış Tarihi</label>
                        <input type="datetime-local" name="planned_pickup_date" id="planned_pickup_date" value="{{ old('planned_pickup_date') }}" class="form-control border-warning-200 focus:border-warning focus:ring-warning">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Planlanan Teslimat Tarihi</label>
                        <input type="datetime-local" name="planned_delivery_date" id="planned_delivery_date" value="{{ old('planned_delivery_date') }}" class="form-control border-warning-200 focus:border-warning focus:ring-warning">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Toplam Ağırlık (kg)</label>
                        <input type="number" step="0.01" name="total_weight" id="total_weight" value="{{ old('total_weight') }}" class="form-control border-warning-200 focus:border-warning focus:ring-warning">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Toplam Hacim (m³)</label>
                        <input type="number" step="0.01" name="total_volume" id="total_volume" value="{{ old('total_volume') }}" class="form-control border-warning-200 focus:border-warning focus:ring-warning">
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
                        <textarea name="notes" id="notes" class="form-control border-warning-200 focus:border-warning focus:ring-warning" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-warning-200);">
                    <a href="{{ route('admin.orders.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
                    <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Sipariş Oluştur</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 sticky-top" style="border-color: var(--bs-warning-200); top: 1rem;">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">summarize</span>
                Sipariş Özeti
            </h5>
            <div class="order-summary-content">
                <div class="mb-3">
                    <span class="text-secondary small d-block">Müşteri</span>
                    <span id="summary_customer" class="fw-semibold">—</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Alış Adresi</span>
                    <span id="summary_pickup" class="small text-break">—</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Teslimat Adresi</span>
                    <span id="summary_delivery" class="small text-break">—</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Planlanan Alış</span>
                    <span id="summary_pickup_date">—</span>
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
    const customerSelect = document.getElementById('customer_id');
    const pickupSelect = document.getElementById('pickup_address_select');
    const deliverySelect = document.getElementById('delivery_address_select');
    const pickupTextarea = document.getElementById('pickup_address');
    const deliveryTextarea = document.getElementById('delivery_address');
    const orderForm = document.getElementById('orderForm');

    const summary = {
        customer: document.getElementById('summary_customer'),
        pickup: document.getElementById('summary_pickup'),
        delivery: document.getElementById('summary_delivery'),
        pickupDate: document.getElementById('summary_pickup_date'),
        deliveryDate: document.getElementById('summary_delivery_date'),
        weightVolume: document.getElementById('summary_weight_volume'),
        dangerous: document.getElementById('summary_dangerous'),
    };

    const url = '{{ route("admin.orders.customer-addresses") }}';

    function loadAddresses() {
        const customerId = customerSelect.value || '0';
        fetch(`${url}?customer_id=${customerId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            pickupSelect.innerHTML = '<option value="">— Kayıtlı adreslerden seç veya manuel gir —</option>';
            deliverySelect.innerHTML = '<option value="">— Kayıtlı adreslerden seç veya manuel gir —</option>';

            (data.pickup || []).forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.address;
                opt.textContent = a.name;
                pickupSelect.appendChild(opt);
            });
            (data.delivery || []).forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.address;
                opt.textContent = a.name;
                deliverySelect.appendChild(opt);
            });
        })
        .catch(() => {});
    }

    function updateSummary() {
        const custOpt = customerSelect.options[customerSelect.selectedIndex];
        summary.customer.textContent = custOpt ? custOpt.text : '—';
        summary.pickup.textContent = pickupTextarea.value || '—';
        summary.delivery.textContent = deliveryTextarea.value || '—';
        const pd = document.getElementById('planned_pickup_date').value;
        summary.pickupDate.textContent = pd ? new Date(pd + 'Z').toLocaleString('tr-TR') : '—';
        const dd = document.getElementById('planned_delivery_date').value;
        summary.deliveryDate.textContent = dd ? new Date(dd + 'Z').toLocaleString('tr-TR') : '—';
        const w = document.getElementById('total_weight').value;
        const v = document.getElementById('total_volume').value;
        summary.weightVolume.textContent = [w, v].filter(Boolean).length ? `${w || '—'} kg / ${v || '—'} m³` : '—';
        summary.dangerous.textContent = document.getElementById('is_dangerous').checked ? 'Evet' : 'Hayır';
    }

    customerSelect.addEventListener('change', function () {
        pickupSelect.selectedIndex = 0;
        deliverySelect.selectedIndex = 0;
        pickupTextarea.value = '';
        deliveryTextarea.value = '';
        loadAddresses();
        updateSummary();
    });

    pickupSelect.addEventListener('change', function () {
        if (this.value) pickupTextarea.value = this.value;
        updateSummary();
    });
    deliverySelect.addEventListener('change', function () {
        if (this.value) deliveryTextarea.value = this.value;
        updateSummary();
    });

    ['pickup_address', 'delivery_address', 'planned_pickup_date', 'planned_delivery_date', 'total_weight', 'total_volume', 'is_dangerous'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateSummary);
        if (el) el.addEventListener('change', updateSummary);
    });

    loadAddresses();
    updateSummary();
});
</script>
@endsection
