@extends('layouts.app')

@section('title', 'Yeni İş Ortağı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni İş Ortağı</h2>
        <p class="text-secondary mb-0">SAP Business Partner uyumlu kayıt oluşturun</p>
    </div>
    <a href="{{ route('admin.business-partners.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('admin.business-partners.store') }}" method="POST">
        @csrf
        <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <span class="material-symbols-outlined text-primary">handshake</span>
            İş Ortağı Bilgileri
        </h4>
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Firma <span class="text-danger">*</span></label>
                <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                    <option value="">Firma Seçin</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">İş Ortağı Türü <span class="text-danger">*</span></label>
                <select name="partner_type" class="form-select @error('partner_type') is-invalid @enderror" required>
                    <option value="">Seçin</option>
                    <option value="customer" {{ old('partner_type') === 'customer' ? 'selected' : '' }}>Müşteri</option>
                    <option value="vendor"   {{ old('partner_type') === 'vendor'   ? 'selected' : '' }}>Tedarikçi</option>
                    <option value="carrier"  {{ old('partner_type') === 'carrier'  ? 'selected' : '' }}>Taşıyıcı</option>
                    <option value="both"     {{ old('partner_type') === 'both'     ? 'selected' : '' }}>Müşteri & Tedarikçi</option>
                </select>
                @error('partner_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">Ad <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Kısa Ad</label>
                <input type="text" name="short_name" value="{{ old('short_name') }}" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Vergi No</label>
                <input type="text" name="tax_number" value="{{ old('tax_number') }}" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Vergi Dairesi</label>
                <input type="text" name="tax_office" value="{{ old('tax_office') }}" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">E-posta</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Para Birimi <span class="text-danger">*</span></label>
                <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                    <option value="TRY" {{ old('currency', 'TRY') === 'TRY' ? 'selected' : '' }}>TRY</option>
                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                </select>
                @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Ödeme Koşulu</label>
                <select name="payment_terms" class="form-select">
                    <option value="">Seçin</option>
                    <option value="IMMEDIATE" {{ old('payment_terms') === 'IMMEDIATE' ? 'selected' : '' }}>Peşin</option>
                    <option value="NET30"     {{ old('payment_terms') === 'NET30'     ? 'selected' : '' }}>NET30</option>
                    <option value="NET60"     {{ old('payment_terms') === 'NET60'     ? 'selected' : '' }}>NET60</option>
                    <option value="NET90"     {{ old('payment_terms') === 'NET90'     ? 'selected' : '' }}>NET90</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Kredi Limiti</label>
                <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Pasif</option>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Adres</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Notlar</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top">
            <a href="{{ route('admin.business-partners.index') }}" class="btn btn-light">İptal</a>
            <button type="submit" class="btn btn-primary">Oluştur</button>
        </div>
    </form>
</div>
@endsection
