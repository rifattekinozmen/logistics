@extends('layouts.app')

@section('title', 'Yeni Müşteri - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Müşteri Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir müşteri kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.customers.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-info-200);">
    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Müşteri Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('name') is-invalid border-danger @enderror" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">E-posta</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('email') is-invalid border-danger @enderror">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('phone') is-invalid border-danger @enderror">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Vergi Numarası</label>
                <input type="text" name="tax_number" value="{{ old('tax_number') }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('tax_number') is-invalid border-danger @enderror">
                @error('tax_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Adres</label>
                <textarea name="address" class="form-control border-info-200 focus:border-info focus:ring-info @error('address') is-invalid border-danger @enderror" rows="3">{{ old('address') }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-info-200 focus:border-info focus:ring-info @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Pasif</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-info-200);">
            <a href="{{ route('admin.customers.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Müşteri Oluştur</button>
        </div>
    </form>
</div>
@endsection
