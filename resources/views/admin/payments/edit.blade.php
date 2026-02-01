@extends('layouts.app')

@section('title', 'Ödeme Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Ödeme Düzenle</h2>
        <p class="text-secondary mb-0">Ödeme bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.payments.update', $payment->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="payment_type" class="form-label fw-semibold text-dark">Ödeme Türü <span class="text-danger">*</span></label>
                <select name="payment_type" id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                    <option value="income" {{ old('payment_type', $payment->payment_type) === 'income' ? 'selected' : '' }}>Gelir</option>
                    <option value="expense" {{ old('payment_type', $payment->payment_type) === 'expense' ? 'selected' : '' }}>Gider</option>
                </select>
                @error('payment_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="amount" class="form-label fw-semibold text-dark">Tutar (₺) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $payment->amount) }}" required>
                @error('amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="due_date" class="form-label fw-semibold text-dark">Vade Tarihi <span class="text-danger">*</span></label>
                <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $payment->due_date?->format('Y-m-d')) }}" required>
                @error('due_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="paid_date" class="form-label fw-semibold text-dark">Ödeme Tarihi</label>
                <input type="date" name="paid_date" id="paid_date" class="form-control @error('paid_date') is-invalid @enderror" value="{{ old('paid_date', $payment->paid_date?->format('Y-m-d')) }}">
                @error('paid_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="0" {{ old('status', $payment->status) == 0 ? 'selected' : '' }}>Bekliyor</option>
                    <option value="1" {{ old('status', $payment->status) == 1 ? 'selected' : '' }}>Ödendi</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="payment_method" class="form-label fw-semibold text-dark">Ödeme Yöntemi</label>
                <input type="text" name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" value="{{ old('payment_method', $payment->payment_method) }}">
                @error('payment_method')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="reference_number" class="form-label fw-semibold text-dark">Referans No</label>
                <input type="text" name="reference_number" id="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number', $payment->reference_number) }}">
                @error('reference_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="notes" class="form-label fw-semibold text-dark">Notlar</label>
                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $payment->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-outline-secondary">İptal</a>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
</div>
@endsection
