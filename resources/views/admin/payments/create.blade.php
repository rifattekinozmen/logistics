@extends('layouts.app')

@section('title', 'Yeni Ödeme - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Ödeme Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir ödeme kaydı oluşturun</p>
        @if(isset($order) && $order)
            <a href="{{ route('admin.orders.show', $order->id) }}" class="small text-decoration-none mt-1 d-inline-block">
                ← Sipariş #{{ $order->order_number }} sayfasına dön
            </a>
        @endif
    </div>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <form action="{{ route('admin.payments.store') }}" method="POST">
        @csrf

        <input type="hidden" name="related_type" value="{{ \App\Models\Customer::class }}">
        @if(isset($order) && $order)
        <input type="hidden" name="from_order" value="{{ $order->id }}">
        @endif

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Müşteri <span class="text-danger">*</span></label>
                <select name="related_id" id="related_id" class="form-select @error('related_id') is-invalid @enderror" required>
                    <option value="">Müşteri Seçin</option>
                    @foreach($customers ?? [] as $customer)
                    <option value="{{ $customer->id }}" {{ old('related_id', $selectedCustomer?->id) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                    @endforeach
                </select>
                @error('related_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Ödeme Türü <span class="text-danger">*</span></label>
                <select name="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                    <option value="incoming" {{ old('payment_type', 'incoming') === 'incoming' ? 'selected' : '' }}>Gelir</option>
                    <option value="outgoing" {{ old('payment_type') === 'outgoing' ? 'selected' : '' }}>Gider</option>
                </select>
                @error('payment_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Tutar (₺) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $order?->freight_price) }}" class="form-control @error('amount') is-invalid @enderror" required>
                @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Vade Tarihi <span class="text-danger">*</span></label>
                <input type="date" name="due_date" value="{{ old('due_date', now()->format('Y-m-d')) }}" class="form-control @error('due_date') is-invalid @enderror" required>
                @error('due_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="0" {{ old('status', isset($order) ? '1' : '0') == '0' ? 'selected' : '' }}>Beklemede</option>
                    <option value="1" {{ old('status', isset($order) ? '1' : '0') == '1' ? 'selected' : '' }}>Ödendi</option>
                    <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>Gecikmiş</option>
                    <option value="3" {{ old('status') == '3' ? 'selected' : '' }}>İptal</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Notlar</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="{{ isset($order) && $order ? 'Sipariş #' . $order->order_number . ' için ödeme' : '' }}">{{ old('notes', isset($order) && $order ? 'Sipariş #' . $order->order_number . ' için ödeme' : '') }}</textarea>
                @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
            <a href="{{ isset($order) && $order ? route('admin.orders.show', $order->id) : route('admin.payments.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Ödeme Oluştur</button>
        </div>
    </form>
</div>
@endsection
