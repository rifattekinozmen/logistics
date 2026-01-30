@extends('layouts.app')

@section('title', 'Dashboard - Logistics')

@section('page-title', 'Hoş Geldiniz, ' . Auth::user()->name . '!')
@section('page-subtitle', 'Logistics yönetim paneline hoş geldiniz.')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 transition-all hover:shadow-md" style="border-color: var(--bs-primary-200);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-primary-200 rounded-2xl d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">local_shipping</span>
                </div>
                <span class="badge bg-success-200 text-success px-2 py-1 rounded-pill small fw-bold">+12%</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">1,234</h3>
            <p class="small fw-semibold text-secondary mb-0">Aktif Sevkiyat</p>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 transition-all hover:shadow-md" style="border-color: var(--bs-info-200);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-info-200 rounded-2xl d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <span class="material-symbols-outlined text-info" style="font-size: 1.5rem;">warehouse</span>
                </div>
                <span class="badge bg-info-200 text-info px-2 py-1 rounded-pill small fw-bold">+8%</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">567</h3>
            <p class="small fw-semibold text-secondary mb-0">Depo Stok</p>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 transition-all hover:shadow-md" style="border-color: var(--bs-warning-200);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-warning-200 rounded-2xl d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <span class="material-symbols-outlined text-warning" style="font-size: 1.5rem;">assignment</span>
                </div>
                <span class="badge bg-warning-200 text-warning px-2 py-1 rounded-pill small fw-bold">+5%</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">89</h3>
            <p class="small fw-semibold text-secondary mb-0">Bekleyen Sipariş</p>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4 h-100 transition-all hover:shadow-md" style="border-color: var(--bs-success-200);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-success-200 rounded-2xl d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <span class="material-symbols-outlined text-success" style="font-size: 1.5rem;">groups</span>
                </div>
                <span class="badge bg-success-200 text-success px-2 py-1 rounded-pill small fw-bold">+15%</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">42</h3>
            <p class="small fw-semibold text-secondary mb-0">Aktif Müşteri</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="h3 fw-bold text-dark mb-0">Son Aktiviteler</h3>
                <button class="btn btn-link text-primary fw-semibold p-0 text-decoration-none" style="font-size: 0.875rem;">
                    Tümünü Gör
                </button>
            </div>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3 p-3 rounded-2xl bg-primary-200 transition-all hover:shadow-sm">
                    <div class="bg-primary rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">local_shipping</span>
                    </div>
                    <div class="grow">
                        <p class="small fw-bold text-dark mb-0">Yeni sevkiyat oluşturuldu</p>
                        <p class="small text-secondary mb-0">2 dakika önce</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 p-3 rounded-2xl bg-info-200 transition-all hover:shadow-sm">
                    <div class="bg-info rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">inventory</span>
                    </div>
                    <div class="grow">
                        <p class="small fw-bold text-dark mb-0">Stok güncellemesi yapıldı</p>
                        <p class="small text-secondary mb-0">15 dakika önce</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 p-3 rounded-2xl bg-warning-200 transition-all hover:shadow-sm">
                    <div class="bg-warning rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">assignment</span>
                    </div>
                    <div class="grow">
                        <p class="small fw-bold text-dark mb-0">Yeni sipariş alındı</p>
                        <p class="small text-secondary mb-0">1 saat önce</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 p-3 rounded-2xl bg-success-200 transition-all hover:shadow-sm">
                    <div class="bg-success rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">person_add</span>
                    </div>
                    <div class="grow">
                        <p class="small fw-bold text-dark mb-0">Yeni müşteri kaydı</p>
                        <p class="small text-secondary mb-0">2 saat önce</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h3 fw-bold text-dark mb-4">Hızlı Erişim</h3>
            <div class="d-flex flex-column gap-3">
                <a href="{{ route('identity.form') }}" class="d-flex align-items-center gap-3 p-3 rounded-2xl border text-decoration-none bg-primary-200 border-primary-200 transition-all hover:shadow-sm hover:bg-primary hover:text-white">
                    <div class="bg-primary rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined">verified_user</span>
                    </div>
                    <div class="grow">
                        <p class="small fw-bold text-dark mb-0">Kimlik Doğrulama</p>
                        <p class="small text-secondary mb-0">MERNİS entegrasyonu</p>
                    </div>
                    <span class="material-symbols-outlined text-secondary">arrow_forward</span>
                </a>
                <a href="#" class="d-flex align-items-center gap-3 p-3 rounded-2xl bg-info-200 border-info-200 text-decoration-none transition-all hover:shadow-sm hover:bg-info hover:text-white">
                    <div class="bg-info rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined">local_shipping</span>
                    </div>
                    <div class="grow">
                        <p class="small fw-bold text-dark mb-0">Sevkiyat Yönetimi</p>
                        <p class="small text-secondary mb-0">Sevkiyatları görüntüle</p>
                    </div>
                    <span class="material-symbols-outlined text-secondary">arrow_forward</span>
                </a>
                <a href="#" class="d-flex align-items-center gap-3 p-3 rounded-2xl bg-warning-200 border-warning-200 text-decoration-none transition-all hover:shadow-sm hover:bg-warning hover:text-white">
                    <div class="bg-warning rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined">warehouse</span>
                    </div>
                    <div class="grow">
                        <p class="small fw-bold text-dark mb-0">Depo Yönetimi</p>
                        <p class="small text-secondary mb-0">Stokları yönet</p>
                    </div>
                    <span class="material-symbols-outlined text-secondary">arrow_forward</span>
                </a>
                <a href="#" class="d-flex align-items-center gap-3 p-3 rounded-2xl bg-success-200 border-success-200 text-decoration-none transition-all hover:shadow-sm hover:bg-success hover:text-white">
                    <div class="bg-success rounded-3xl d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined">assignment</span>
                    </div>
                    <div class="grow">
                        <p class="small fw-bold text-dark mb-0">Sipariş Yönetimi</p>
                        <p class="small text-secondary mb-0">Siparişleri görüntüle</p>
                    </div>
                    <span class="material-symbols-outlined text-secondary">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
