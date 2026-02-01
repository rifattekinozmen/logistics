@extends('layouts.app')

@section('title', 'Personel Detay - Logistics')
@section('page-title', 'Personel Detay')
@section('page-subtitle', $personel->ad_soyad)

@section('content')
<div class="personel-portal-wrap">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Personel Detay</h2>
            <p class="text-secondary mb-0 small">{{ $personel->ad_soyad }} — bilgileri görüntüleniyor.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('personel.edit', $personel->id) }}" class="btn btn-personel-primary d-inline-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                Düzenle
            </a>
            <a href="{{ route('personel.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
                <span class="material-symbols-outlined">arrow_back</span>
                Geri Dön
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Sol sütun: Bilgi kartları --}}
        <div class="col-lg-6">
            <div class="personel-form-card">
                <h4 class="h5 fw-bold text-dark mb-2">Kişisel Bilgiler</h4>
                <p class="text-secondary small mb-4">Personel kişisel iletişim bilgileri.</p>

                <div class="text-center mb-4">
                    <div class="personel-photo-placeholder">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">T.C. Kimlik No</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->tckn ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Kimlik Seri No</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->kimlik_seri_no ?? '—' }}</p>
                    </div>
                    <div class="col-12">
                        <p class="small text-secondary mb-1">Ad Soyad</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->ad_soyad }}</p>
                    </div>
                    <div class="col-12">
                        <p class="small text-secondary mb-1">E-posta</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->email ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Telefon</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->telefon ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Mobil Telefon</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->mobil_telefon ?? '—' }}</p>
                    </div>
                    <div class="col-12">
                        <p class="small text-secondary mb-1">Acil Durum İletişim</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->acil_iletisim ?? '—' }}</p>
                    </div>
                    <div class="col-12">
                        <p class="small text-secondary mb-1">Durum</p>
                        <span class="badge bg-{{ $personel->aktif ? 'primary' : 'secondary' }} px-3 py-2 rounded-pill">
                            {{ $personel->aktif ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="personel-form-card mt-4">
                <h4 class="h5 fw-bold text-dark mb-2">Nüfus Kaydı</h4>
                <p class="text-secondary small mb-4">Anne, baba ve doğum bilgileri.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Anne Adı</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->anne_adi ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Baba Adı</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->baba_adi ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Doğum Tarihi</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->dogum_tarihi ? $personel->dogum_tarihi->format('d.m.Y') : '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Doğum Yeri</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->dogum_yeri ?? '—' }}</p>
                    </div>
                    <div class="col-12">
                        <p class="small text-secondary mb-1">Medeni Durum</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->medeni_durum ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="personel-form-card mt-4">
                <h4 class="h5 fw-bold text-dark mb-2">İş Bilgileri</h4>
                <p class="text-secondary small mb-4">Departman ve pozisyon bilgileri.</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Departman</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->departman ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Pozisyon</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->pozisyon ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">İşe Başlama Tarihi</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->ise_baslama_tarihi ? $personel->ise_baslama_tarihi->format('d.m.Y') : '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="small text-secondary mb-1">Maaş</p>
                        <p class="fw-semibold text-dark mb-0">{{ $personel->maas ? number_format($personel->maas, 2, ',', '.') . ' ₺' : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sağ sütun: Belge önizleme kartı --}}
        <div class="col-lg-6">
            <div class="personel-preview-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="h5 fw-bold text-dark mb-0">Belge Önizleme</h4>
                    <span class="personel-live-badge">Doğrulandı</span>
                </div>
                <p class="text-secondary small mb-4">Personel bilgileri aşağıdaki kartta özetlenmiştir.</p>

                <div class="personel-id-card mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="personel-photo-placeholder shrink-0" style="width: 72px; height: 72px;">
                            <span class="material-symbols-outlined" style="font-size: 32px;">person</span>
                        </div>
                        <div class="grow">
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">T.C. KİMLİK NO</span>
                                <span class="personel-id-value">{{ $personel->tckn ?? '—' }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">SERİ NO</span>
                                <span class="personel-id-value">{{ $personel->kimlik_seri_no ?? '—' }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">AD SOYAD</span>
                                <span class="personel-id-value">{{ $personel->ad_soyad }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">E-POSTA</span>
                                <span class="personel-id-value text-break">{{ $personel->email ?? '—' }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">TELEFON / MOBİL</span>
                                <span class="personel-id-value">{{ $personel->telefon ? ($personel->mobil_telefon ? $personel->telefon . ' / ' . $personel->mobil_telefon : $personel->telefon) : ($personel->mobil_telefon ?? '—') }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">DOĞUM TARİHİ</span>
                                <span class="personel-id-value">{{ $personel->dogum_tarihi ? $personel->dogum_tarihi->format('d.m.Y') : '—' }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">DOĞUM YERİ</span>
                                <span class="personel-id-value">{{ $personel->dogum_yeri ?? '—' }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">MEDENİ DURUM</span>
                                <span class="personel-id-value">{{ $personel->medeni_durum ?? '—' }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">DEPARTMAN</span>
                                <span class="personel-id-value">{{ $personel->departman ?? '—' }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">POZİSYON</span>
                                <span class="personel-id-value">{{ $personel->pozisyon ?? '—' }}</span>
                            </div>
                            <div class="personel-id-row mb-1">
                                <span class="personel-id-label">İŞE BAŞLAMA</span>
                                <span class="personel-id-value">{{ $personel->ise_baslama_tarihi ? $personel->ise_baslama_tarihi->format('d.m.Y') : '—' }}</span>
                            </div>
                            <div class="personel-id-row">
                                <span class="personel-id-label">MAAŞ</span>
                                <span class="personel-id-value">{{ $personel->maas ? number_format($personel->maas, 2, ',', '.') . ' ₺' : '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="personel-verified-box bg-white rounded-3 p-3 border border-primary-200 mb-4">
                    <p class="small text-secondary mb-0">
                        <span class="material-symbols-outlined align-middle me-1 text-primary" style="font-size: 1rem;">verified</span>
                        Personel verileri güvenli şekilde saklanmaktadır. KVKK uyumludur.
                    </p>
                </div>

                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('personel.edit', $personel->id) }}" class="btn btn-personel-primary w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                        Düzenle
                    </a>
                    <form action="{{ route('personel.destroy', $personel->id) }}" method="POST" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2 rounded-3">
                            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                            Sil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-3 border-top border-primary-200">
        <div class="personel-footer-info">
            <span class="material-symbols-outlined" style="font-size: 1.1rem;">lock</span>
            <span>Veri şifrelidir</span>
            <span class="text-secondary">•</span>
            <span>KVKK uyumlu</span>
        </div>
    </div>
</div>
@endsection
