@extends('layouts.app')

@section('title', 'Personel Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel Detayı</h2>
        <p class="text-secondary mb-0">{{ $personnel->ad_soyad }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.personnel.edit', $personnel) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.personnel.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <h3 class="h4 fw-bold text-dark mb-4">Personel Bilgileri</h3>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">Ad Soyad</label>
            <p class="fw-bold text-dark mb-0">{{ $personnel->ad_soyad }}</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">Durum</label>
            <div>
                <span class="badge bg-{{ $personnel->aktif ? 'primary-200 text-primary' : 'secondary-200 text-secondary' }} px-3 py-2 rounded-pill fw-semibold">
                    {{ $personnel->aktif ? 'Aktif' : 'Pasif' }}
                </span>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">E-posta</label>
            <p class="text-dark mb-0">{{ $personnel->email ?? '-' }}</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">Telefon</label>
            <p class="text-dark mb-0">{{ $personnel->telefon ?? '-' }}</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">Mobil Telefon</label>
            <p class="text-dark mb-0">{{ $personnel->mobil_telefon ?? '-' }}</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">Departman</label>
            <p class="text-dark mb-0">{{ $personnel->departman ?? '-' }}</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">Pozisyon</label>
            <p class="text-dark mb-0">{{ $personnel->pozisyon ?? '-' }}</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">İşe Başlama Tarihi</label>
            <p class="text-dark mb-0">{{ $personnel->ise_baslama_tarihi ? $personnel->ise_baslama_tarihi->format('d.m.Y') : '-' }}</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">Maaş</label>
            <p class="text-dark mb-0">{{ $personnel->maas ? number_format($personnel->maas, 2, ',', '.') . ' ₺' : '-' }}</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-secondary">T.C. Kimlik No</label>
            <p class="text-dark mb-0">{{ $personnel->tckn ?? '-' }}</p>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
        <a href="{{ route('admin.personnel.edit', $personnel) }}" class="btn btn-primary">Düzenle</a>
        <form action="{{ route('admin.personnel.destroy', $personnel) }}" method="POST" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Sil</button>
        </form>
    </div>
</div>
@endsection
