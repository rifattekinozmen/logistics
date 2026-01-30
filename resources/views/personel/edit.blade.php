@extends('layouts.app')

@section('title', 'Personel Düzenle - Logistics')
@section('page-title', 'Personel Düzenle')
@section('page-subtitle', $personel->ad_soyad)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel Düzenle</h2>
        <p class="text-secondary mb-0">{{ $personel->ad_soyad }}</p>
    </div>
    <a href="{{ route('personel.index') }}" class="btn btn-light">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <form action="{{ route('personel.update', $personel->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Ad Soyad <span class="text-danger">*</span></label>
                <input type="text" name="ad_soyad" value="{{ old('ad_soyad', $personel->ad_soyad) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('ad_soyad') is-invalid border-danger @enderror" required>
                @error('ad_soyad')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">E-posta <span class="text-danger">*</span></label>
                <input type="email" name="email" value="{{ old('email', $personel->email) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('email') is-invalid border-danger @enderror" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Telefon</label>
                <input type="text" name="telefon" value="{{ old('telefon', $personel->telefon) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('telefon') is-invalid border-danger @enderror">
                @error('telefon')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Departman <span class="text-danger">*</span></label>
                <input type="text" name="departman" value="{{ old('departman', $personel->departman) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('departman') is-invalid border-danger @enderror" required>
                @error('departman')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Pozisyon <span class="text-danger">*</span></label>
                <input type="text" name="pozisyon" value="{{ old('pozisyon', $personel->pozisyon) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('pozisyon') is-invalid border-danger @enderror" required>
                @error('pozisyon')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">İşe Başlama Tarihi <span class="text-danger">*</span></label>
                <input type="date" name="ise_baslama_tarihi" value="{{ old('ise_baslama_tarihi', $personel->ise_baslama_tarihi?->format('Y-m-d')) }}" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('ise_baslama_tarihi') is-invalid border-danger @enderror" required>
                @error('ise_baslama_tarihi')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Maaş</label>
                <input type="number" name="maas" value="{{ old('maas', $personel->maas) }}" step="0.01" min="0" class="form-control border-primary-200 focus:border-primary focus:ring-primary @error('maas') is-invalid border-danger @enderror">
                @error('maas')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', $personel->aktif) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold text-dark" for="aktif">
                        Aktif
                    </label>
                </div>
            </div>

            <div class="col-12">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('personel.index') }}" class="btn btn-light">İptal</a>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        Güncelle
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
