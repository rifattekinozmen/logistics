@extends('layouts.app')

@section('title', 'Yeni Personel - Logistics')
@section('page-title', 'Yeni Personel Ekle')
@section('page-subtitle', 'Yeni bir personel kaydı oluşturun')

@section('content')
<div class="personel-portal-wrap">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Yeni Personel Ekle</h2>
            <p class="text-secondary mb-0 small">Resmi kimlik bilgilerini ve iş bilgilerini giriniz.</p>
        </div>
        <a href="{{ route('personel.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>

    <form action="{{ route('personel.store') }}" method="POST" id="personel-form">
        @csrf

        <div class="row g-4">
            {{-- Sol sütun: Form --}}
            <div class="col-lg-6">
                <div class="personel-form-card">
                    <h4 class="h5 fw-bold text-dark mb-2">Kişisel Bilgiler</h4>
                    <p class="text-secondary small mb-4">Personel bilgilerini aşağıdaki alanlara giriniz.</p>

                    {{-- Fotoğraf yer tutucusu (referans tasarım) --}}
                    <div class="text-center mb-4">
                        <div class="personel-photo-placeholder">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                        <p class="small text-secondary mb-0">Profil fotoğrafı (isteğe bağlı)</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">T.C. Kimlik No</label>
                            <input type="text" name="tckn" id="tckn" value="{{ old('tckn') }}" class="form-control rounded-3 border-primary-200 @error('tckn') is-invalid border-danger @enderror" placeholder="00000000000" maxlength="11" pattern="[0-9]*" inputmode="numeric">
                            @error('tckn')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">11 haneli, sadece rakam</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Kimlik Seri No</label>
                            <input type="text" name="kimlik_seri_no" id="kimlik_seri_no" value="{{ old('kimlik_seri_no') }}" class="form-control rounded-3 border-primary-200 @error('kimlik_seri_no') is-invalid border-danger @enderror" placeholder="A01-XXXXXX">
                            @error('kimlik_seri_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Ad Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="ad_soyad" id="ad_soyad" value="{{ old('ad_soyad') }}" class="form-control rounded-3 border-primary-200 @error('ad_soyad') is-invalid border-danger @enderror" placeholder="Adınız Soyadınız" required>
                            @error('ad_soyad')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">E-posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control rounded-3 border-primary-200 @error('email') is-invalid border-danger @enderror" placeholder="ornek@firma.com" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Telefon</label>
                            <input type="text" name="telefon" id="telefon" value="{{ old('telefon') }}" class="form-control rounded-3 border-primary-200 @error('telefon') is-invalid border-danger @enderror" placeholder="5XX XXX XX XX">
                            @error('telefon')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Mobil Telefon</label>
                            <input type="text" name="mobil_telefon" id="mobil_telefon" value="{{ old('mobil_telefon') }}" class="form-control rounded-3 border-primary-200 @error('mobil_telefon') is-invalid border-danger @enderror" placeholder="5XX XXX XX XX">
                            @error('mobil_telefon')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Acil Durum İletişim</label>
                            <input type="text" name="acil_iletisim" id="acil_iletisim" value="{{ old('acil_iletisim') }}" class="form-control rounded-3 border-primary-200 @error('acil_iletisim') is-invalid border-danger @enderror" placeholder="Acil durumda aranacak numara">
                            @error('acil_iletisim')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="personel-form-card mt-4">
                    <h4 class="h5 fw-bold text-dark mb-2">Nüfus Kaydı</h4>
                    <p class="text-secondary small mb-4">Anne, baba ve doğum bilgileri.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Anne Adı</label>
                            <input type="text" name="anne_adi" id="anne_adi" value="{{ old('anne_adi') }}" class="form-control rounded-3 border-primary-200 @error('anne_adi') is-invalid border-danger @enderror" placeholder="Anne adı">
                            @error('anne_adi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Baba Adı</label>
                            <input type="text" name="baba_adi" id="baba_adi" value="{{ old('baba_adi') }}" class="form-control rounded-3 border-primary-200 @error('baba_adi') is-invalid border-danger @enderror" placeholder="Baba adı">
                            @error('baba_adi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Doğum Tarihi</label>
                            <input type="date" name="dogum_tarihi" id="dogum_tarihi" value="{{ old('dogum_tarihi') }}" class="form-control rounded-3 border-primary-200 @error('dogum_tarihi') is-invalid border-danger @enderror">
                            @error('dogum_tarihi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Doğum Yeri</label>
                            <input type="text" name="dogum_yeri" id="dogum_yeri" value="{{ old('dogum_yeri') }}" class="form-control rounded-3 border-primary-200 @error('dogum_yeri') is-invalid border-danger @enderror" placeholder="İl / ilçe">
                            @error('dogum_yeri')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Medeni Durum</label>
                            <select name="medeni_durum" id="medeni_durum" class="form-select rounded-3 border-primary-200 @error('medeni_durum') is-invalid border-danger @enderror">
                                <option value="">Seçiniz</option>
                                <option value="Bekar" {{ old('medeni_durum') === 'Bekar' ? 'selected' : '' }}>Bekar</option>
                                <option value="Evli" {{ old('medeni_durum') === 'Evli' ? 'selected' : '' }}>Evli</option>
                                <option value="Dul" {{ old('medeni_durum') === 'Dul' ? 'selected' : '' }}>Dul</option>
                                <option value="Boşanmış" {{ old('medeni_durum') === 'Boşanmış' ? 'selected' : '' }}>Boşanmış</option>
                            </select>
                            @error('medeni_durum')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="personel-form-card mt-4">
                    <h4 class="h5 fw-bold text-dark mb-2">İş Bilgileri</h4>
                    <p class="text-secondary small mb-4">Departman ve pozisyon bilgilerini giriniz.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Departman <span class="text-danger">*</span></label>
                            <input type="text" name="departman" id="departman" value="{{ old('departman') }}" class="form-control rounded-3 border-primary-200 @error('departman') is-invalid border-danger @enderror" placeholder="Örn. Lojistik" required>
                            @error('departman')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Pozisyon <span class="text-danger">*</span></label>
                            <input type="text" name="pozisyon" id="pozisyon" value="{{ old('pozisyon') }}" class="form-control rounded-3 border-primary-200 @error('pozisyon') is-invalid border-danger @enderror" placeholder="Örn. Sürücü" required>
                            @error('pozisyon')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">İşe Başlama Tarihi <span class="text-danger">*</span></label>
                            <input type="date" name="ise_baslama_tarihi" id="ise_baslama_tarihi" value="{{ old('ise_baslama_tarihi') }}" class="form-control rounded-3 border-primary-200 @error('ise_baslama_tarihi') is-invalid border-danger @enderror" required>
                            @error('ise_baslama_tarihi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Maaş</label>
                            <input type="number" name="maas" id="maas" value="{{ old('maas') }}" step="0.01" min="0" class="form-control rounded-3 border-primary-200 @error('maas') is-invalid border-danger @enderror" placeholder="0,00">
                            @error('maas')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="aktif">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sağ sütun: Canlı önizleme --}}
            <div class="col-lg-6">
                <div class="personel-preview-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="h5 fw-bold text-dark mb-0">Canlı Önizleme</h4>
                        <span class="personel-live-badge">Senkronize</span>
                    </div>
                    <p class="text-secondary small mb-4">Girdiğiniz bilgiler aşağıdaki kartta anlık görüntülenir.</p>

                    <div class="personel-id-card mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="personel-photo-placeholder shrink-0" style="width: 72px; height: 72px;">
                                <span class="material-symbols-outlined" style="font-size: 32px;">person</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">T.C. KİMLİK NO</span>
                                    <span class="personel-id-value" id="preview-tckn">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">SERİ NO</span>
                                    <span class="personel-id-value" id="preview-kimlik_seri_no">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">AD SOYAD</span>
                                    <span class="personel-id-value" id="preview-ad_soyad">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">E-POSTA</span>
                                    <span class="personel-id-value text-break" id="preview-email">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">TELEFON / MOBİL</span>
                                    <span class="personel-id-value" id="preview-telefon">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">DOĞUM TARİHİ</span>
                                    <span class="personel-id-value" id="preview-dogum_tarihi">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">DOĞUM YERİ</span>
                                    <span class="personel-id-value" id="preview-dogum_yeri">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">MEDENİ DURUM</span>
                                    <span class="personel-id-value" id="preview-medeni_durum">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">DEPARTMAN</span>
                                    <span class="personel-id-value" id="preview-departman">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">POZİSYON</span>
                                    <span class="personel-id-value" id="preview-pozisyon">—</span>
                                </div>
                                <div class="personel-id-row mb-1">
                                    <span class="personel-id-label">İŞE BAŞLAMA</span>
                                    <span class="personel-id-value" id="preview-ise_baslama_tarihi">—</span>
                                </div>
                                <div class="personel-id-row">
                                    <span class="personel-id-label">MAAŞ</span>
                                    <span class="personel-id-value" id="preview-maas">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="personel-verified-box bg-white rounded-3 p-3 border border-primary-200">
                        <p class="small text-secondary mb-0">
                            <span class="material-symbols-outlined align-middle me-1 text-primary" style="font-size: 1rem;">verified</span>
                            Personel verileri güvenli şekilde saklanacaktır. KVKK uyumludur.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alt bilgi + butonlar --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-3 border-top border-primary-200">
            <div class="personel-footer-info">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">lock</span>
                <span>Veri şifrelidir</span>
                <span class="text-secondary">•</span>
                <span>KVKK uyumlu</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('personel.index') }}" class="btn btn-light rounded-3">İptal</a>
                <button type="submit" class="btn btn-secondary rounded-3">Taslak Kaydet</button>
                <button type="submit" class="btn btn-personel-primary d-inline-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                    Onayla ve Devam Et
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('personel-form');
    if (!form) return;
    var fields = ['tckn', 'kimlik_seri_no', 'ad_soyad', 'email', 'telefon', 'mobil_telefon', 'acil_iletisim', 'anne_adi', 'baba_adi', 'dogum_tarihi', 'dogum_yeri', 'medeni_durum', 'departman', 'pozisyon', 'ise_baslama_tarihi', 'maas'];
    function formatDate(str) {
        if (!str) return '—';
        var d = new Date(str);
        if (isNaN(d.getTime())) return str;
        return d.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    function formatMoney(val) {
        if (val === '' || val === null || val === undefined) return '—';
        var n = parseFloat(val);
        if (isNaN(n)) return '—';
        return n.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
    }
    function getVal(id) { var el = document.getElementById(id); return (el && el.value) ? el.value.trim() : ''; }
    function setPreview(id, text) { var el = document.getElementById(id); if (el) el.textContent = text || '—'; }
    function updatePreview() {
        var telefonStr = getVal('telefon') || getVal('mobil_telefon') || '—';
        if (getVal('telefon') && getVal('mobil_telefon')) telefonStr = getVal('telefon') + ' / ' + getVal('mobil_telefon');
        setPreview('preview-tckn', getVal('tckn'));
        setPreview('preview-kimlik_seri_no', getVal('kimlik_seri_no'));
        setPreview('preview-ad_soyad', getVal('ad_soyad'));
        setPreview('preview-email', getVal('email'));
        setPreview('preview-telefon', telefonStr);
        setPreview('preview-dogum_tarihi', getVal('dogum_tarihi') ? formatDate(document.getElementById('dogum_tarihi').value) : '');
        setPreview('preview-dogum_yeri', getVal('dogum_yeri'));
        setPreview('preview-medeni_durum', getVal('medeni_durum'));
        setPreview('preview-departman', getVal('departman'));
        setPreview('preview-pozisyon', getVal('pozisyon'));
        setPreview('preview-ise_baslama_tarihi', getVal('ise_baslama_tarihi') ? formatDate(document.getElementById('ise_baslama_tarihi').value) : '');
        setPreview('preview-maas', getVal('maas') ? formatMoney(document.getElementById('maas').value) : '');
    }
    fields.forEach(function (name) {
        var el = document.getElementById(name);
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });
    updatePreview();
})();
</script>
@endpush
@endsection
