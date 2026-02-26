@extends('layouts.app')

@section('title', 'Personel Detayı - Logistics')

@section('styles')
@include('admin.personnel._personnel_styles')
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Personel Detayı</h2>
        <p class="text-secondary mb-0">Personel bilgilerini görüntüleyin ve yönetin</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.personnel.edit', $personnel) }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.personnel.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Sol sütun: Ana içerik --}}
    <div class="col-lg-7">
        @include('admin.personnel._header', ['personnel' => $personnel])

        {{-- Kişisel Bilgiler --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">info</span>
                Kişisel Bilgiler
            </h4>
            <div class="row g-3">
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">description</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Personel Kodu</label><p class="text-dark mb-0">{{ $personnel->personel_kodu ?? $personnel->id }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">badge</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">T.C. Kimlik No</label><p class="text-dark mb-0">{{ $personnel->tckn ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">calendar_today</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Doğum Tarihi</label><p class="text-dark mb-0">@if($personnel->dogum_tarihi){{ $personnel->dogum_tarihi->format('d.m.Y') }}@if($personnel->dogum_tarihi->age) <span class="text-secondary">({{ $personnel->dogum_tarihi->age }} yaşında)</span>@endif @else - @endif</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">bloodtype</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Kan Grubu</label><p class="text-dark mb-0">{{ $personnel->kan_grubu ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">group</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Medeni Durum</label><p class="text-dark mb-0">{{ $personnel->medeni_durum ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">trending_up</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Durum</label><div><span class="badge bg-success-200 text-success px-2 py-1 rounded-pill fw-semibold">{{ $personnel->aktif ? 'Aktif' : 'Pasif' }}</span></div></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">male</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Cinsiyet</label><p class="text-dark mb-0">{{ $personnel->cinsiyet ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">person</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Baba Adı</label><p class="text-dark mb-0">{{ $personnel->baba_adi ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">person</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Ana Adı</label><p class="text-dark mb-0">{{ $personnel->anne_adi ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">location_on</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Doğum Yeri</label><p class="text-dark mb-0">{{ $personnel->dogum_yeri ?? $personnel->city?->name_tr ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">family_restroom</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Çocuk Sayısı</label><p class="text-dark mb-0">{{ $personnel->cocuk_sayisi ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- İletişim Bilgileri --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">call</span>
                İletişim Bilgileri
            </h4>
            <div class="row g-3">
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">call</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Telefon</label><p class="text-dark mb-0">{{ $personnel->telefon ?? $personnel->mobil_telefon ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">mail</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">E-posta</label><p class="text-dark mb-0">{{ $personnel->email ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">emergency</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Acil Durum İletişim</label><p class="text-dark mb-0">{{ $personnel->acil_iletisim ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Adres Bilgileri --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">location_on</span>
                Adres Bilgileri
            </h4>
            <div class="row g-3">
                <div class="col-12 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">home</span>
                    <div class="flex-grow-1"><label class="form-label small fw-semibold text-secondary mb-0">Adres 1. Satır</label><p class="text-dark mb-0">{{ $personnel->adres_satir_1 ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">public</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Ülke</label><p class="text-dark mb-0">{{ $personnel->country?->name_tr ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">location_city</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Şehir</label><p class="text-dark mb-0">{{ $personnel->city?->name_tr ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">map</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">İlçe</label><p class="text-dark mb-0">{{ $personnel->district?->name_tr ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">signpost</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Bulvar</label><p class="text-dark mb-0">{{ $personnel->bulvar ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">route</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Sokak</label><p class="text-dark mb-0">{{ $personnel->sokak ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">door_front</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Dış Kapı</label><p class="text-dark mb-0">{{ $personnel->dis_kapi ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">door_sliding</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">İç Kapı</label><p class="text-dark mb-0">{{ $personnel->ic_kapi ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">markunread_mailbox</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Posta Kodu</label><p class="text-dark mb-0">{{ $personnel->posta_kodu ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- İş Bilgileri --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">work</span>
                İş Bilgileri
            </h4>
            <div class="row g-3">
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">business</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Departman</label><p class="text-dark mb-0">{{ $personnel->departman ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">work_history</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Pozisyon</label><p class="text-dark mb-0">{{ $personnel->pozisyon ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">event</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">İşe Başlama Tarihi</label><p class="text-dark mb-0">{{ $personnel->ise_baslama_tarihi?->format('d.m.Y') ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">health_and_safety</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">SGK Başlangıç Tarihi</label><p class="text-dark mb-0">{{ $personnel->sgk_baslangic_tarihi?->format('d.m.Y') ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">payments</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Maaş</label><p class="text-dark mb-0">{{ $personnel->maas ? number_format($personnel->maas, 2, ',', '.') . ' ₺' : '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">event_note</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Başvuru Tarihi</label><p class="text-dark mb-0">{{ $personnel->basvuru_tarihi?->format('d.m.Y') ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">task_alt</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Referans Tarihi</label><p class="text-dark mb-0">{{ $personnel->referans_tarihi?->format('d.m.Y') ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">fact_check</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Çalışma Durumu</label><p class="text-dark mb-0">{{ $personnel->calisma_durumu ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Kimlik Bilgileri --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">badge</span>
                Kimlik Bilgileri
            </h4>
            <div class="row g-3">
                @foreach([
                    ['icon' => 'description', 'label' => 'Cüzdan Seri No', 'value' => $personnel->kimlik_seri_no],
                    ['icon' => 'folder', 'label' => 'Cilt No', 'value' => $personnel->cilt_no],
                    ['icon' => 'group', 'label' => 'Aile Sıra No', 'value' => $personnel->aile_sira_no],
                    ['icon' => 'format_list_numbered', 'label' => 'Sıra No', 'value' => $personnel->sira_no],
                    ['icon' => 'note', 'label' => 'Cüzdan Kayıt No', 'value' => $personnel->cuzdan_kayit_no],
                    ['icon' => 'calendar_today', 'label' => 'Veriliş Tarihi', 'value' => $personnel->verilis_tarihi?->format('d.m.Y')],
                ] as $field)
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">{{ $field['icon'] }}</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">{{ $field['label'] }}</label><p class="text-dark mb-0">{{ $field['value'] ?? '-' }}</p></div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Eğitim Bilgileri --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">school</span>
                Eğitim Bilgileri
            </h4>
            <div class="row g-3">
                @foreach([
                    ['icon' => 'menu_book', 'label' => 'Tahsil Durumu', 'value' => $personnel->tahsil_durumu],
                    ['icon' => 'account_balance', 'label' => 'Mezun Olduğu Okul', 'value' => $personnel->mezun_okul],
                    ['icon' => 'category', 'label' => 'Mezun Olduğu Bölüm', 'value' => $personnel->mezun_bolum],
                    ['icon' => 'event', 'label' => 'Mezuniyet Tarihi', 'value' => $personnel->mezuniyet_tarihi?->format('d.m.Y')],
                    ['icon' => 'translate', 'label' => 'Bildiği Yab. Dil', 'value' => $personnel->bildigi_dil],
                ] as $field)
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">{{ $field['icon'] }}</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">{{ $field['label'] }}</label><p class="text-dark mb-0">{{ $field['value'] ?? '-' }}</p></div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Askerlik Bilgileri --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">military_tech</span>
                Askerlik Bilgileri
            </h4>
            <div class="row g-3">
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">shield</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Askerlik Durumu</label><p class="text-dark mb-0">{{ $personnel->askerlik_durumu ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">military_tech</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Askerlik Türü</label><p class="text-dark mb-0">{{ $personnel->askerlik_turu ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">event</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Askerlik Başlangıç Tarihi</label><p class="text-dark mb-0">{{ $personnel->askerlik_baslangic_tarihi?->format('d.m.Y') ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">event_available</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Askerlik Bitiş Tarihi</label><p class="text-dark mb-0">{{ $personnel->askerlik_bitis_tarihi?->format('d.m.Y') ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- SGK Bilgileri --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">health_and_safety</span>
                SGK Bilgileri
            </h4>
            <div class="row g-3">
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">elderly</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Yaşlılık Aylığı</label><p class="text-dark mb-0">{{ isset($personnel->sgk_yaslilik_ayligi) ? ($personnel->sgk_yaslilik_ayligi ? 'Evet' : 'Hayır') : '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">event_busy</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">30 Günden Az (E/H)</label><p class="text-dark mb-0">{{ isset($personnel->sgk_30_gunden_az) ? ($personnel->sgk_30_gunden_az ? 'Evet' : 'Hayır') : '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">policy</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">SGK Sigorta Kodu</label><p class="text-dark mb-0">{{ $personnel->sgk_sigorta_kodu ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">description</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">SGK Sigorta Adı</label><p class="text-dark mb-0">{{ $personnel->sgk_sigorta_adi ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">work</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">ÇSGB İş Kolu Kodu</label><p class="text-dark mb-0">{{ $personnel->csgb_is_kolu_kodu ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">work_outline</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">ÇSGB İş Kolu Adı</label><p class="text-dark mb-0">{{ $personnel->csgb_is_kolu_adi ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">badge</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">2821 Görev Kodu</label><p class="text-dark mb-0">{{ $personnel->kanun_2821_gorev_kodu ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">assignment</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">2821 Görev Adı</label><p class="text-dark mb-0">{{ $personnel->kanun_2821_gorev_adi ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">badge</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Meslek Kodu</label><p class="text-dark mb-0">{{ $personnel->meslek_kodu ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">person</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Meslek Adı</label><p class="text-dark mb-0">{{ $personnel->meslek_adi ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Banka Bilgileri --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">account_balance</span>
                Banka Bilgileri
            </h4>
            <div class="row g-3">
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">account_balance</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Banka Adı</label><p class="text-dark mb-0">{{ $personnel->banka_adi ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">store</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Şube Kodu</label><p class="text-dark mb-0">{{ $personnel->sube_kodu ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">credit_card</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Hesap Numarası</label><p class="text-dark mb-0">{{ $personnel->hesap_no ?? '-' }}</p></div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">payments</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Maaş Ödeme Türü</label><p class="text-dark mb-0">{{ $personnel->maas_odeme_turu ?? '-' }}</p></div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <span class="material-symbols-outlined text-secondary mt-1">receipt_long</span>
                    <div class="flex-grow-1"><label class="form-label small fw-semibold text-secondary mb-0">IBAN</label><p class="text-dark mb-0">{{ $personnel->iban ?? '-' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Notlar --}}
        @if($personnel->notlar)
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">note</span>
                Notlar
            </h4>
            <p class="text-dark mb-0">{{ $personnel->notlar }}</p>
        </div>
        @endif
    </div>

    {{-- Sağ sütun: Sidebar + Kimlik kartı --}}
    <div class="col-lg-5">
        {{-- Kimlik Kartı --}}
        <div class="personel-preview-card border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">badge</span>
                Kimlik Kartı
            </h4>
            @include('admin.personnel._id_card', ['personnel' => $personnel, 'live' => false])
        </div>

        {{-- İşlemler --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">settings</span>
                İşlemler
            </h4>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.personnel.edit', $personnel) }}" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">edit</span> Düzenle
                </a>
                <a href="{{ route('admin.documents.index', ['documentable_type' => \App\Models\Personel::class, 'documentable_id' => $personnel->id]) }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2 text-start">
                    <span class="material-symbols-outlined">description</span> Belgeler ({{ $personnel->documents_count ?? 0 }})
                </a>
                <form action="{{ route('admin.personnel.destroy', $personnel) }}" method="POST" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link text-danger p-0 d-inline-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">delete</span> Personeli Sil
                    </button>
                </form>
            </div>
        </div>

        {{-- İstatistikler --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">analytics</span>
                İstatistikler
            </h4>
            <div class="row g-2">
                <div class="col-6">
                    <div class="border rounded-2 p-3 text-center bg-primary-200">
                        <div class="h4 fw-bold text-primary mb-0">{{ $personnel->documents_count ?? 0 }}</div>
                        <small class="text-secondary">Belge</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded-2 p-3 text-center bg-primary-200">
                        <div class="h4 fw-bold text-primary mb-0">{{ $personnel->personnel_attendances_count ?? 0 }}</div>
                        <small class="text-secondary">Devam</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded-2 p-3 text-center bg-primary-200">
                        <div class="h4 fw-bold text-primary mb-0">0</div>
                        <small class="text-secondary">İş Emri</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bilgi --}}
        <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">info</span>
                Bilgi
            </h4>
            <div class="d-flex flex-column gap-2">
                <div class="d-flex gap-2 align-items-start">
                    <span class="material-symbols-outlined text-secondary mt-1">add_circle</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Oluşturulma Tarihi</label><p class="text-dark mb-0">{{ $personnel->created_at?->format('d.m.Y H:i') ?? '-' }}</p></div>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <span class="material-symbols-outlined text-secondary mt-1">update</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Son Düzenleme Tarihi</label><p class="text-dark mb-0">{{ $personnel->updated_at?->format('d.m.Y H:i') ?? '-' }}</p></div>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <span class="material-symbols-outlined text-secondary mt-1">work</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">İşe Başlama</label><p class="text-dark mb-0">{{ $personnel->ise_baslama_tarihi?->format('d.m.Y') ?? '-' }}</p></div>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <span class="material-symbols-outlined text-secondary mt-1">business</span>
                    <div><label class="form-label small fw-semibold text-secondary mb-0">Departman</label><p class="text-dark mb-0">{{ $personnel->departman ?? '-' }}</p></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
