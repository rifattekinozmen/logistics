@extends('layouts.app')

@section('title', 'Kimlik Doğrulama Sonucu - Logistics')
@section('page-title', 'Doğrulama Sonucu')
@section('page-subtitle', 'Kimlik doğrulama işleminizin sonucu')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <div class="text-center mb-4">
                <div class="rounded-3xl d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: var(--bs-success-200);">
                    <span class="material-symbols-outlined text-success" style="font-size: 2.5rem;">verified</span>
                </div>
                <h3 class="h4 fw-bold text-dark mb-2">Kimlik Doğrulama</h3>
                <p class="text-secondary mb-0">MERNİS üzerinden kimlik bilgilerinizin doğrulama sonucu.</p>
            </div>

            <div class="alert alert-info rounded-2xl d-flex align-items-start gap-3" role="alert">
                <span class="material-symbols-outlined flex-shrink-0">info</span>
                <div>
                    <p class="fw-semibold mb-1">Yakında</p>
                    <p class="small mb-0">Doğrulama sonuç sayfası MERNİS entegrasyonu tamamlandığında aktif olacaktır.</p>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-center mt-4">
                <a href="{{ route('identity.form') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Forma Dön
                </a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
