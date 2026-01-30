@extends('layouts.app')

@section('title', 'Personel Detay - Logistics')
@section('page-title', 'Personel Detay')
@section('page-subtitle', $personel->ad_soyad)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel Detay</h2>
        <p class="text-secondary mb-0">{{ $personel->ad_soyad }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('personel.edit', $personel->id) }}" class="btn btn-primary d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('personel.index') }}" class="btn btn-light">
            <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">Kişisel Bilgiler</h4>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Ad Soyad</p>
                    <p class="fw-semibold text-dark mb-0">{{ $personel->ad_soyad }}</p>
                </div>
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">E-posta</p>
                    <p class="fw-semibold text-dark mb-0">{{ $personel->email ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Telefon</p>
                    <p class="fw-semibold text-dark mb-0">{{ $personel->telefon ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Durum</p>
                    <span class="badge bg-{{ $personel->aktif ? 'success-200 text-success' : 'secondary-200 text-secondary' }} px-3 py-2 rounded-pill">
                        {{ $personel->aktif ? 'Aktif' : 'Pasif' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4 mt-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">İş Bilgileri</h4>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Departman</p>
                    <p class="fw-semibold text-dark mb-0">{{ $personel->departman ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Pozisyon</p>
                    <p class="fw-semibold text-dark mb-0">{{ $personel->pozisyon ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">İşe Başlama Tarihi</p>
                    <p class="fw-semibold text-dark mb-0">{{ $personel->ise_baslama_tarihi ? $personel->ise_baslama_tarihi->format('d.m.Y') : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="small text-secondary mb-1">Maaş</p>
                    <p class="fw-semibold text-dark mb-0">{{ $personel->maas ? number_format($personel->maas, 2, ',', '.') . ' ₺' : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
            <h4 class="h5 fw-bold text-dark mb-4">İşlemler</h4>
            
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('personel.edit', $personel->id) }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                    Düzenle
                </a>
                <form action="{{ route('personel.destroy', $personel->id) }}" method="POST" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                        Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
