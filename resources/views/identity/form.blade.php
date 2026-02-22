@extends('layouts.app')

@section('title', 'Kimlik Doğrulama - Logistics')
@section('page-title', 'Kimlik Doğrulama')
@section('page-subtitle', 'MERNİS entegrasyonu ile kimlik bilgilerinizi doğrulayın')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <div class="text-center mb-4">
                <div class="rounded-3xl d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: var(--bs-primary-200);">
                    <span class="material-symbols-outlined text-primary" style="font-size: 2.5rem;">verified_user</span>
                </div>
                <h3 class="h4 fw-bold text-dark mb-2">MERNİS Kimlik Doğrulama</h3>
                <p class="text-secondary mb-0">Bu özellik MERNİS entegrasyonu ile kimlik bilgilerinizi doğrulamanızı sağlar.</p>
            </div>

            <div class="alert alert-info rounded-2xl d-flex align-items-start gap-3" role="alert">
                <span class="material-symbols-outlined flex-shrink-0">info</span>
                <div>
                    <p class="fw-semibold mb-1">Yakında</p>
                    <p class="small mb-0">Kimlik doğrulama formu henüz aktif değildir. MERNİS entegrasyonu tamamlandığında bu sayfa üzerinden işlem yapılabilecektir.</p>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Dashboard'a Dön
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
