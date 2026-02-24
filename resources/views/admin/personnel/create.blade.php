@extends('layouts.app')

@section('title', 'Yeni Personel - Logistics')

@section('styles')
@include('admin.personnel._personnel_styles')
@endsection

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

<form action="{{ route('admin.personnel.store') }}" method="POST" id="personnel-create-form" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        {{-- Sol sütun: Form --}}
        <div class="col-lg-7">
            @include('admin.personnel._header', ['personnel' => null, 'editable' => true])

            {{-- Form kartları --}}
            <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-primary-200);">
                @include('admin.personnel._form', ['personnel' => null])

                <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-primary-200);">
                    <a href="{{ route('admin.personnel.index') }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
                    <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Personel Ekle</button>
                </div>
            </div>
        </div>

        {{-- Sağ sütun: Sidebar + Kimlik önizleme --}}
        <div class="col-lg-5">
            {{-- Kimlik Önizleme --}}
            <div class="personel-preview-card border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="h5 fw-bold text-dark mb-0">Canlı Önizleme</h4>
                    <span class="personel-live-badge">Senkronize</span>
                </div>
                <p class="text-secondary small mb-4">Girdiğiniz bilgiler aşağıdaki kartta anlık görüntülenir.</p>

                @include('admin.personnel._id_card', ['personnel' => null, 'live' => true])

                <div class="rounded-3 p-3 border bg-white mt-4" style="border-color: var(--bs-primary-200) !important;">
                    <p class="small text-secondary mb-0">
                        <span class="material-symbols-outlined align-middle me-1 text-primary" style="font-size: 1rem;">verified</span>
                        Personel verileri güvenli şekilde saklanacaktır. KVKK uyumludur.
                    </p>
                </div>
            </div>

            {{-- İşlemler --}}
            <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
                <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-primary">settings</span>
                    İşlemler
                </h4>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.personnel.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">list</span> Personel Listesi
                    </a>
                </div>
            </div>

            {{-- İstatistikler --}}
            <div class="border rounded-3 p-4 bg-white shadow-sm mb-4" style="border-color: var(--bs-primary-200) !important;">
                <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-primary">analytics</span>
                    İstatistikler
                </h4>
                <p class="text-secondary small mb-3">Yeni kayıt oluşturulduktan sonra istatistikler görüntülenecektir.</p>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="border rounded-2 p-3 text-center bg-primary-200">
                            <div class="h4 fw-bold text-primary mb-0">0</div>
                            <small class="text-secondary">Belge</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-2 p-3 text-center bg-primary-200">
                            <div class="h4 fw-bold text-primary mb-0">0</div>
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
                <p class="text-secondary small mb-0">
                    Personel kaydı oluşturulduktan sonra oluşturulma tarihi, son düzenleme tarihi ve diğer bilgiler burada görüntülenecektir.
                </p>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var photoInput = document.getElementById('personnel-photo-input-header');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(ev) {
                    var dataUrl = ev.target.result;
                    var placeholder = document.getElementById('personnel-header-avatar-placeholder');
                    var img = document.getElementById('personnel-header-avatar-img');
                    if (img) {
                        img.src = dataUrl;
                    } else if (placeholder) {
                        placeholder.outerHTML = '<img src="' + dataUrl + '" alt="" class="rounded-circle object-fit-cover personnel-avatar-preview" style="width: 80px; height: 80px;" id="personnel-header-avatar-img">';
                    }
                    var cardImg = document.getElementById('preview-photo-img');
                    var cardPlaceholder = document.getElementById('preview-photo-placeholder');
                    var miniImg = document.getElementById('preview-photo-mini-img');
                    var miniPlaceholder = document.getElementById('preview-photo-mini-placeholder');
                    if (cardImg) { cardImg.src = dataUrl; cardImg.classList.remove('d-none'); }
                    if (cardPlaceholder) cardPlaceholder.classList.add('d-none');
                    if (miniImg) { miniImg.src = dataUrl; miniImg.classList.remove('d-none'); }
                    if (miniPlaceholder) miniPlaceholder.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
<script>
(function () {
    var fields = ['personel_kodu', 'tckn', 'kimlik_seri_no', 'ad_soyad', 'email', 'telefon', 'mobil_telefon', 'acil_iletisim', 'anne_adi', 'baba_adi', 'dogum_tarihi', 'dogum_yeri', 'medeni_durum', 'kan_grubu', 'cinsiyet', 'tahsil_durumu', 'departman', 'pozisyon', 'ise_baslama_tarihi', 'maas', 'son_gecerlilik_tarihi'];
    var cinsiyetMap = { 'Erkek': 'E / M', 'Kadın': 'K / F' };
    function formatDate(str) {
        if (!str) return '—';
        var d = new Date(str);
        if (isNaN(d.getTime())) return str;
        return d.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    function parseAdSoyad(full) {
        var parts = (full || '').trim().split(/\s+/).filter(Boolean);
        if (parts.length <= 1) return { adi: full || '—', soyadi: full || '—' };
        return { adi: parts.slice(0, -1).join(' '), soyadi: parts[parts.length - 1] };
    }
    function getVal(id) { var el = document.getElementById(id); return (el && el.value) ? el.value.trim() : ''; }
    function setPreview(id, text) { var el = document.getElementById(id); if (el) el.textContent = text || '—'; }
    function updatePreview() {
        var adSoyad = parseAdSoyad(getVal('ad_soyad'));
        var countryEl = document.getElementById('country_id');
        var uyrukText = 'T.C. / TUR';
        if (countryEl && countryEl.selectedIndex >= 0) {
            var opt = countryEl.options[countryEl.selectedIndex];
            if (opt && opt.value && opt.dataset.uyruk) uyrukText = opt.dataset.uyruk;
            else if (opt && opt.value) uyrukText = opt.text.trim();
        }
        setPreview('preview-tckn', getVal('tckn'));
        setPreview('preview-soyadi', adSoyad.soyadi.toUpperCase());
        setPreview('preview-adi', adSoyad.adi.toUpperCase());
        setPreview('preview-dogum_tarihi', getVal('dogum_tarihi') ? formatDate(document.getElementById('dogum_tarihi')?.value) : '');
        setPreview('preview-cinsiyet', cinsiyetMap[getVal('cinsiyet')] || getVal('cinsiyet'));
        setPreview('preview-kimlik_seri_no', getVal('kimlik_seri_no'));
        setPreview('preview-son_gecerlilik', getVal('son_gecerlilik_tarihi') ? formatDate(document.getElementById('son_gecerlilik_tarihi')?.value) : '');
        setPreview('preview-uyruk', uyrukText);
        setPreview('preview-anne_adi', getVal('anne_adi'));
        setPreview('preview-baba_adi', getVal('baba_adi'));
    }
    fields.forEach(function (name) {
        var el = document.getElementById(name);
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });
    var countryEl = document.getElementById('country_id');
    if (countryEl) {
        countryEl.addEventListener('change', updatePreview);
    }
    updatePreview();
})();
</script>
@endpush
@endsection
