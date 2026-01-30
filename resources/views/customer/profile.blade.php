@extends('layouts.customer-app')

@section('title', 'Profil - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">person</span>
            <h2 class="h3 fw-bold text-dark mb-0">Profil</h2>
        </div>
        <p class="text-secondary mb-0">Profil bilgilerinizi güncelleyin</p>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <form action="{{ route('customer.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">
                    <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">person</span>
                    Ad
                </label>
                <input type="text" value="{{ $customer->name }}" class="form-control" disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">
                    <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">email</span>
                    E-posta
                </label>
                <input type="email" value="{{ $customer->email }}" class="form-control" disabled>
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label fw-semibold text-dark">
                    <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">phone</span>
                    Telefon
                </label>
                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone) }}">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="address" class="form-label fw-semibold text-dark">
                    <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">home</span>
                    Adres
                </label>
                <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $customer->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @if(Auth::user() && Auth::user()->hasPermission('customer.portal.profile.update'))
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">save</span>
                    Güncelle
                </button>
            </div>
        @endif
    </form>
</div>

<!-- Şifre Değiştirme -->
@if(Auth::user() && Auth::user()->hasPermission('customer.portal.profile.update'))
    <div class="bg-white rounded-3xl shadow-sm border p-4 mt-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">lock</span>
            <h3 class="h5 fw-bold text-dark mb-0">Şifre Değiştir</h3>
        </div>
        <form action="{{ route('customer.profile.change-password') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="current_password" class="form-label fw-semibold text-dark">Mevcut Şifre <span class="text-danger">*</span></label>
                    <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label fw-semibold text-dark">Yeni Şifre <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label fw-semibold text-dark">Yeni Şifre (Tekrar) <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">lock</span>
                    Şifreyi Değiştir
                </button>
            </div>
        </form>
    </div>
@endif

<!-- Hızlı Erişim -->
<div class="bg-white rounded-3xl shadow-sm border p-4 mt-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">settings</span>
        <h3 class="h5 fw-bold text-dark mb-0">Hızlı Erişim</h3>
    </div>
    <div class="row g-3">
        @if(Auth::user() && Auth::user()->hasPermission('customer.portal.favorite-addresses.manage'))
            <div class="col-md-6">
                <a href="{{ route('customer.favorite-addresses.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">location_on</span>
                    Favori Adreslerim
                </a>
            </div>
        @endif
        @if(Auth::user() && Auth::user()->hasPermission('customer.portal.order-templates.manage'))
            <div class="col-md-6">
                <a href="{{ route('customer.order-templates.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">content_copy</span>
                    Sipariş Şablonlarım
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
