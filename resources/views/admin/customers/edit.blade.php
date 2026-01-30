@extends('layouts.app')

@section('title', 'Müşteri Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Müşteri Düzenle</h2>
        <p class="text-secondary mb-0">Müşteri bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-info-200);">
    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Müşteri Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('name') is-invalid border-danger @enderror" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">E-posta</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('email') is-invalid border-danger @enderror">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('phone') is-invalid border-danger @enderror">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Vergi Numarası</label>
                <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('tax_number') is-invalid border-danger @enderror">
                @error('tax_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Adres</label>
                <textarea name="address" class="form-control border-info-200 focus:border-info focus:ring-info @error('address') is-invalid border-danger @enderror" rows="3">{{ old('address', $customer->address) }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-info-200 focus:border-info focus:ring-info @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', $customer->status) == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status', $customer->status) == '0' ? 'selected' : '' }}>Pasif</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-info-200);">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Güncelle</button>
        </div>
    </form>
</div>
@endsection
