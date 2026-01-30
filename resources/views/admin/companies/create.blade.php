@extends('layouts.app')

@section('title', 'Yeni Firma Ekle - Logistics')
@section('page-title', 'Yeni Firma Ekle')
@section('page-subtitle', 'Sisteme yeni bir firma ekleyin')

@section('content')
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show rounded-3xl" role="alert">
    <span class="material-symbols-outlined me-2">error</span>
    <strong>Hata!</strong> Lütfen formu kontrol edin.
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <form action="{{ route('admin.companies.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <!-- Company Name -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Ticari Unvan <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('name') is-invalid border-danger @enderror" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Short Name -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Kısa İsim</label>
                <input type="text" name="short_name" value="{{ old('short_name') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('short_name') is-invalid border-danger @enderror">
                @error('short_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tax Office -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Vergi Dairesi</label>
                <input type="text" name="tax_office" value="{{ old('tax_office') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('tax_office') is-invalid border-danger @enderror">
                @error('tax_office')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tax Number -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Vergi Numarası</label>
                <input type="text" name="tax_number" value="{{ old('tax_number') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('tax_number') is-invalid border-danger @enderror">
                @error('tax_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- MERSIS No -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">MERSIS Numarası</label>
                <input type="text" name="mersis_no" value="{{ old('mersis_no') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('mersis_no') is-invalid border-danger @enderror">
                @error('mersis_no')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Trade Registry No -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Ticaret Sicil Numarası</label>
                <input type="text" name="trade_registry_no" value="{{ old('trade_registry_no') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('trade_registry_no') is-invalid border-danger @enderror">
                @error('trade_registry_no')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Currency -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Para Birimi <span class="text-danger">*</span></label>
                <select name="currency" class="form-select border-primary-200 focus:border-primary focus:ring-primary @error('currency') is-invalid border-danger @enderror" required>
                    <option value="TRY" {{ old('currency', 'TRY') === 'TRY' ? 'selected' : '' }}>TRY - Türk Lirası</option>
                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD - Amerikan Doları</option>
                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                </select>
                @error('currency')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Default VAT Rate -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Varsayılan KDV Oranı (%) <span class="text-danger">*</span></label>
                <input type="number" name="default_vat_rate" value="{{ old('default_vat_rate', 20) }}" step="0.01" min="0" max="100" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('default_vat_rate') is-invalid border-danger @enderror" required>
                @error('default_vat_rate')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Phone -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('phone') is-invalid border-danger @enderror">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">E-posta</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('email') is-invalid border-danger @enderror">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Logo Upload -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Logo</label>
<input type="file" name="logo" accept="image/*,.svg,image/svg+xml" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('logo') is-invalid border-danger @enderror">
                @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-secondary">JPG, PNG, GIF veya SVG (Max: 2MB)</small>
            </div>

            <!-- Stamp Upload -->
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Kaşe/İmza</label>
                <input type="file" name="stamp" accept="image/*" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('stamp') is-invalid border-danger @enderror">
                @error('stamp')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-secondary">JPG, PNG veya GIF (Max: 2MB)</small>
            </div>

            <!-- Is Active -->
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold text-dark" for="is_active">
                        Firma Aktif
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="col-12">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
                        İptal
                    </a>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        Firma Oluştur
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
