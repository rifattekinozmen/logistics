@extends('layouts.app')

@section('title', 'Yeni Personel - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Personel Ekle</h2>
        <p class="text-secondary mb-0">Yeni bir personel kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.personnel.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
    <form action="{{ route('admin.personnel.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <x-form.input name="ad_soyad" label="Ad Soyad" :value="old('ad_soyad')" required />
            </div>
            <div class="col-md-6">
                <x-form.input name="email" type="email" label="E-posta" :value="old('email')" required />
            </div>
            <div class="col-md-6">
                <x-form.input name="telefon" label="Telefon" :value="old('telefon')" />
            </div>
            <div class="col-md-6">
                <x-form.input name="mobil_telefon" label="Mobil Telefon" :value="old('mobil_telefon')" />
            </div>
            <div class="col-md-6">
                <x-form.input name="departman" label="Departman" :value="old('departman')" required />
            </div>
            <div class="col-md-6">
                <x-form.input name="pozisyon" label="Pozisyon" :value="old('pozisyon')" required />
            </div>
            <div class="col-md-6">
                <x-form.input name="ise_baslama_tarihi" type="date" label="İşe Başlama Tarihi" :value="old('ise_baslama_tarihi')" required />
            </div>
            <div class="col-md-6">
                <x-form.input name="maas" type="number" label="Maaş" :value="old('maas')" />
            </div>
            <div class="col-md-6">
                <x-form.select
                    name="aktif"
                    label="Durum"
                    :options="[1 => 'Aktif', 0 => 'Pasif']"
                    :value="old('aktif', 1)"
                />
            </div>
            <div class="col-md-6">
                <x-form.input name="tckn" label="T.C. Kimlik No" :value="old('tckn')" />
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
            <a href="{{ route('admin.personnel.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Personel Ekle</button>
        </div>
    </form>
</div>
@endsection
