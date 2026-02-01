@extends('layouts.app')

@section('title', 'Yeni Ödeme - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Ödeme Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir ödeme kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-danger-200);">
    <form action="{{ route('admin.payments.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Firma <span class="text-danger">*</span></label>
                <select name="company_id" class="form-select border-danger-200 focus:border-danger focus:ring-danger @error('company_id') is-invalid border-danger @enderror" required>
                    <option value="">Firma Seçin</option>
                    @foreach($companies ?? [] as $company)
                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
                @error('company_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Tür <span class="text-danger">*</span></label>
                <select name="type" class="form-select border-danger-200 focus:border-danger focus:ring-danger @error('type') is-invalid border-danger @enderror" required>
                    <option value="">Tür Seçin</option>
                    <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Gelir</option>
                    <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Gider</option>
                </select>
                @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Tutar <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="form-control border-danger-200 focus:border-danger focus:ring-danger @error('amount') is-invalid border-danger @enderror" required>
                @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Vade Tarihi <span class="text-danger">*</span></label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control border-danger-200 focus:border-danger focus:ring-danger @error('due_date') is-invalid border-danger @enderror" required>
                @error('due_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-danger-200 focus:border-danger focus:ring-danger @error('status') is-invalid border-danger @enderror" required>
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                    <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Ödendi</option>
                    <option value="overdue" {{ old('status') === 'overdue' ? 'selected' : '' }}>Gecikmiş</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Açıklama</label>
                <textarea name="description" class="form-control border-danger-200 focus:border-danger focus:ring-danger @error('description') is-invalid border-danger @enderror" rows="3">{{ old('description') }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-danger-200);">
            <a href="{{ route('admin.payments.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Ödeme Oluştur</button>
        </div>
    </form>
</div>
@endsection
