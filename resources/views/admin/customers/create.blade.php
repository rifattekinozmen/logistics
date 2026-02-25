@extends('layouts.app')

@section('title', 'Yeni Müşteri - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Yeni Müşteri Oluştur</h2>
        <p class="text-secondary mb-0">Yeni bir müşteri kaydı oluşturun</p>
    </div>
    <a href="{{ route('admin.customers.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-info-200);">
    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf

        {{-- Temel Bilgiler --}}
        <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <span class="material-symbols-outlined text-primary">info</span>
            Temel Bilgiler
        </h4>
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <x-form.input name="customer_code" label="Müşteri Kodu" :value="old('customer_code')" placeholder="Opsiyonel" />
            </div>
            <div class="col-md-6">
                <x-form.input name="name" label="Müşteri Adı" :value="old('name')" required />
            </div>
            <div class="col-md-6">
                <x-form.select
                    name="customer_type"
                    label="Müşteri Türü"
                    :options="\App\Enums\CustomerType::options()"
                    :value="old('customer_type')"
                    placeholder="Seçiniz..."
                />
            </div>
            <div class="col-md-6">
                <x-form.select
                    name="priority_level"
                    label="Öncelik Seviyesi"
                    :options="\App\Enums\CustomerPriority::options()"
                    :value="old('priority_level')"
                    placeholder="Seçiniz..."
                />
            </div>
            <div class="col-md-6">
                <x-form.input name="contact_person" label="İletişim Kişisi" :value="old('contact_person')" placeholder="533 123 45 67" />
            </div>
            <div class="col-md-6">
                <x-form.select
                    name="status"
                    label="Durum"
                    :options="[1 => 'Aktif', 0 => 'Pasif']"
                    :value="old('status', 1)"
                    required
                />
            </div>
        </div>

        {{-- İletişim Bilgileri --}}
        <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <span class="material-symbols-outlined text-primary">call</span>
            İletişim Bilgileri
        </h4>
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <x-form.input name="phone" type="tel" label="Telefon" :value="old('phone')" placeholder="530 136 38 01" />
            </div>
            <div class="col-md-6">
                <x-form.input name="email" type="email" label="E-posta" :value="old('email')" />
            </div>
        </div>

        {{-- Vergi Bilgileri --}}
        <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <span class="material-symbols-outlined text-primary">receipt_long</span>
            Vergi Bilgileri
        </h4>
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <x-form.input name="tax_number" label="Vergi Numarası" :value="old('tax_number')" placeholder="1234567890" />
            </div>
            <div class="col-md-6">
                <x-form.select
                    name="tax_office_id"
                    label="Vergi Dairesi"
                    :options="$taxOffices ?? []"
                    :value="old('tax_office_id')"
                    placeholder="Seçiniz..."
                />
            </div>
            <div class="col-md-12">
                <x-form.textarea name="address" label="Adres" :value="old('address')" :rows="3" />
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-info-200);">
            <a href="{{ route('admin.customers.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Müşteri Oluştur</button>
        </div>
    </form>
</div>
@endsection
