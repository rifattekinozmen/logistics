@php
    $personnel = $personnel ?? null;
    $countries = $countries ?? collect();
    $cities = $cities ?? collect();
    $districts = $districts ?? collect();
    $departments = $departments ?? collect();
    $positions = $positions ?? collect();
    $banks = $banks ?? [];
@endphp

<div class="row g-4">
    {{-- Kişisel Bilgiler --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">info</span>
                Kişisel Bilgiler
            </h4>
            <p class="text-secondary small mb-4">Personelin temel kimlik bilgilerini giriniz.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <x-form.input name="personel_kodu" label="Personel Kodu" :value="old('personel_kodu', $personnel?->personel_kodu)" placeholder="Opsiyonel" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="ad_soyad" label="Ad Soyad" :value="old('ad_soyad', $personnel?->ad_soyad)" required />
                </div>
                <div class="col-md-6">
                    <x-form.input name="tckn" label="T.C. Kimlik No" :value="old('tckn', $personnel?->tckn)" maxlength="11" placeholder="11 haneli" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="dogum_tarihi" type="date" label="Doğum Tarihi" :value="old('dogum_tarihi', $personnel?->dogum_tarihi?->format('Y-m-d'))" />
                </div>
                <div class="col-md-6">
                    <x-form.select name="kan_grubu" label="Kan Grubu" :options="\App\Enums\KanGrubu::options()" :value="old('kan_grubu', $personnel?->kan_grubu)" placeholder="Seçiniz..." />
                </div>
                <div class="col-md-6">
                    <x-form.select name="medeni_durum" label="Medeni Durum" :options="\App\Enums\MedeniDurum::options()" :value="old('medeni_durum', $personnel?->medeni_durum)" placeholder="Seçiniz..." />
                </div>
                <div class="col-md-6">
                    <x-form.select name="aktif" label="Durum" :options="[1 => 'Aktif', 0 => 'Pasif']" :value="old('aktif', $personnel ? ($personnel->aktif ? 1 : 0) : 1)" placeholder="Seçiniz..." />
                </div>
                <div class="col-md-6">
                    <x-form.select name="cinsiyet" label="Cinsiyet" :options="\App\Enums\Cinsiyet::options()" :value="old('cinsiyet', $personnel?->cinsiyet)" placeholder="Seçiniz..." />
                </div>
                <div class="col-md-6">
                    <x-form.input name="baba_adi" label="Baba Adı" :value="old('baba_adi', $personnel?->baba_adi)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="anne_adi" label="Ana Adı" :value="old('anne_adi', $personnel?->anne_adi)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="dogum_yeri" label="Doğum Yeri" :value="old('dogum_yeri', $personnel?->dogum_yeri)" placeholder="Şehir adı" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="cocuk_sayisi" type="number" label="Çocuk Sayısı" :value="old('cocuk_sayisi', $personnel?->cocuk_sayisi)" min="0" max="20" placeholder="0" />
                </div>
            </div>
        </div>
    </div>

    {{-- İletişim Bilgileri --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">call</span>
                İletişim Bilgileri
            </h4>
            <p class="text-secondary small mb-4">İletişim bilgilerini giriniz.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <x-form.input name="telefon" label="Telefon" :value="old('telefon', $personnel?->telefon)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="mobil_telefon" label="Mobil Telefon" :value="old('mobil_telefon', $personnel?->mobil_telefon)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="email" type="email" label="E-posta" :value="old('email', $personnel?->email)" required />
                </div>
                <div class="col-md-6">
                    <x-form.input name="acil_iletisim" label="Acil Durum İletişim" :value="old('acil_iletisim', $personnel?->acil_iletisim)" />
                </div>
            </div>
        </div>
    </div>

    {{-- Adres Bilgileri --}}
    <div class="col-12" x-data="personnelAddressCascade()" x-init="init()">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">location_on</span>
                Adres Bilgileri
            </h4>
            <p class="text-secondary small mb-4">Adres detaylarını giriniz.</p>
            <div class="row g-4">
                <div class="col-12">
                    <x-form.input name="adres_satir_1" label="Adres 1. Satır" :value="old('adres_satir_1', $personnel?->adres_satir_1)" placeholder="Mahalle, sokak vb." />
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="country_id" class="form-label fw-semibold text-dark">Ülke</label>
                        <select name="country_id" id="country_id" class="form-select border-info-200 focus:border-info focus:ring-info" x-model="selectedCountryId" @change="onCountryChange($event)">
                            <option value="">Seçiniz...</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" data-uyruk="{{ $c->name_tr }} / {{ $c->code }}" {{ old('country_id', $personnel?->country_id) == $c->id ? 'selected' : '' }}>{{ $c->name_tr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="city_id" class="form-label fw-semibold text-dark">Şehir</label>
                        <select name="city_id" id="city_id" class="form-select border-info-200 focus:border-info focus:ring-info" x-model="currentCityId" x-ref="citySelect" @change="onCityChange($event)">
                            <option value="">Seçiniz...</option>
                            <template x-for="city in filteredCities" :key="city.id">
                                <option :value="city.id" x-text="city.name_tr"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="district_id" class="form-label fw-semibold text-dark">İlçe</label>
                        <select name="district_id" id="district_id" class="form-select border-info-200 focus:border-info focus:ring-info" x-model="currentDistrictId" x-ref="districtSelect">
                            <option value="">Seçiniz...</option>
                            <template x-for="district in filteredDistricts" :key="district.id">
                                <option :value="district.id" x-text="district.name_tr"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <x-form.input name="bulvar" label="Bulvar" :value="old('bulvar', $personnel?->bulvar)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="sokak" label="Sokak" :value="old('sokak', $personnel?->sokak)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="dis_kapi" label="Dış Kapı" :value="old('dis_kapi', $personnel?->dis_kapi)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="ic_kapi" label="İç Kapı" :value="old('ic_kapi', $personnel?->ic_kapi)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="posta_kodu" label="Posta Kodu" :value="old('posta_kodu', $personnel?->posta_kodu)" />
                </div>
            </div>
        </div>
    </div>

    {{-- İş Bilgileri --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">work</span>
                İş Bilgileri
            </h4>
            <p class="text-secondary small mb-4">Departman ve pozisyon bilgilerini giriniz.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <x-form.select name="departman" label="Departman" :options="$departments" :value="old('departman', $personnel?->departman)" placeholder="Seçiniz..." required />
                </div>
                <div class="col-md-6">
                    <x-form.select name="pozisyon" label="Pozisyon" :options="$positions" :value="old('pozisyon', $personnel?->pozisyon)" placeholder="Seçiniz..." required />
                </div>
                <div class="col-md-6">
                    <x-form.input name="ise_baslama_tarihi" type="date" label="İşe Başlama Tarihi" :value="old('ise_baslama_tarihi', $personnel?->ise_baslama_tarihi?->format('Y-m-d'))" required />
                </div>
                <div class="col-md-6">
                    <x-form.input name="sgk_baslangic_tarihi" type="date" label="SGK Başlangıç Tarihi" :value="old('sgk_baslangic_tarihi', $personnel?->sgk_baslangic_tarihi?->format('Y-m-d'))" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="maas" type="number" label="Maaş" :value="old('maas', $personnel?->maas)" step="0.01" min="0" placeholder="0.00" />
                </div>
            </div>
        </div>
    </div>

    {{-- Kimlik Bilgileri --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">badge</span>
                Kimlik Bilgileri
            </h4>
            <p class="text-secondary small mb-4">Nüfus cüzdanı bilgilerini giriniz.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <x-form.input name="kimlik_seri_no" label="Cüzdan Seri No" :value="old('kimlik_seri_no', $personnel?->kimlik_seri_no)" placeholder="Örn. OA142538" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="cilt_no" label="Cilt No" :value="old('cilt_no', $personnel?->cilt_no)" placeholder="Örn. 009" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="aile_sira_no" label="Aile Sıra No" :value="old('aile_sira_no', $personnel?->aile_sira_no)" placeholder="Örn. 0040" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="sira_no" label="Sıra No" :value="old('sira_no', $personnel?->sira_no)" placeholder="Örn. 001523" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="cuzdan_kayit_no" label="Cüzdan Kayıt No" :value="old('cuzdan_kayit_no', $personnel?->cuzdan_kayit_no)" placeholder="Örn. 08546" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="verilis_tarihi" type="date" label="Veriliş Tarihi" :value="old('verilis_tarihi', $personnel?->verilis_tarihi?->format('Y-m-d'))" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="son_gecerlilik_tarihi" type="date" label="Son Geçerlilik Tarihi" :value="old('son_gecerlilik_tarihi', $personnel?->son_gecerlilik_tarihi?->format('Y-m-d'))" placeholder="Kimlik geçerlilik bitiş tarihi" />
                </div>
            </div>
        </div>
    </div>

    {{-- Eğitim Bilgileri --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">school</span>
                Eğitim Bilgileri
            </h4>
            <p class="text-secondary small mb-4">Eğitim geçmişi bilgilerini giriniz.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <x-form.select name="tahsil_durumu" label="Tahsil Durumu" :options="\App\Enums\TahsilDurumu::options()" :value="old('tahsil_durumu', $personnel?->tahsil_durumu)" placeholder="Seçiniz..." />
                </div>
                <div class="col-md-6">
                    <x-form.input name="mezun_okul" label="Mezun Olduğu Okul" :value="old('mezun_okul', $personnel?->mezun_okul)" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="mezun_bolum" label="Mezun Olduğu Bölüm" :value="old('mezun_bolum', $personnel?->mezun_bolum)" placeholder="Örn. LİSE" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="mezuniyet_tarihi" type="date" label="Mezuniyet Tarihi" :value="old('mezuniyet_tarihi', $personnel?->mezuniyet_tarihi?->format('Y-m-d'))" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="bildigi_dil" label="Bildiği Yabancı Dil" :value="old('bildigi_dil', $personnel?->bildigi_dil)" placeholder="Örn. İNGİLİZCE" />
                </div>
            </div>
        </div>
    </div>

    {{-- Askerlik Bilgileri --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">military_tech</span>
                Askerlik Bilgileri
            </h4>
            <p class="text-secondary small mb-4">Askerlik durumu bilgisini giriniz.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <x-form.select name="askerlik_durumu" label="Askerlik Durumu" :options="\App\Enums\AskerlikDurumu::options()" :value="old('askerlik_durumu', $personnel?->askerlik_durumu)" placeholder="Seçiniz..." />
                </div>
            </div>
        </div>
    </div>

    {{-- SGK Bilgileri --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">health_and_safety</span>
                SGK Bilgileri
            </h4>
            <p class="text-secondary small mb-4">SGK ile ilgili bilgileri giriniz.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <x-form.select name="sgk_yaslilik_ayligi" label="Yaşlılık Aylığı" :options="[0 => 'Hayır', 1 => 'Evet']" :value="old('sgk_yaslilik_ayligi', $personnel?->sgk_yaslilik_ayligi)" placeholder="Seçiniz..." />
                </div>
                <div class="col-md-6">
                    <x-form.select name="sgk_30_gunden_az" label="30 Günden Az (E/H)" :options="[0 => 'Hayır', 1 => 'Evet']" :value="old('sgk_30_gunden_az', $personnel?->sgk_30_gunden_az)" placeholder="Seçiniz..." />
                </div>
            </div>
        </div>
    </div>

    {{-- Banka Bilgileri --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">account_balance</span>
                Banka Bilgileri
            </h4>
            <p class="text-secondary small mb-4">Maaş ödeme bilgilerini giriniz.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <x-form.select name="banka_adi" label="Banka Adı" :options="$banks" :value="old('banka_adi', $personnel?->banka_adi)" placeholder="Seçiniz..." />
                </div>
                <div class="col-md-6">
                    <x-form.input name="sube_kodu" label="Şube Kodu" :value="old('sube_kodu', $personnel?->sube_kodu)" placeholder="Örn. 0051" />
                </div>
                <div class="col-md-6">
                    <x-form.input name="hesap_no" label="Hesap Numarası" :value="old('hesap_no', $personnel?->hesap_no)" />
                </div>
                <div class="col-md-6">
                    <x-form.select name="maas_odeme_turu" label="Maaş Ödeme Türü" :options="\App\Enums\MaasOdemeTuru::options()" :value="old('maas_odeme_turu', $personnel?->maas_odeme_turu)" placeholder="Seçiniz..." />
                </div>
                <div class="col-12">
                    <x-form.input name="iban" label="IBAN" :value="old('iban', $personnel?->iban)" placeholder="TR..." maxlength="34" />
                </div>
            </div>
        </div>
    </div>

    {{-- Notlar --}}
    <div class="col-12">
        <div class="border rounded-3 p-4 bg-white shadow-sm" style="border-color: var(--bs-primary-200) !important;">
            <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">note</span>
                Notlar
            </h4>
            <p class="text-secondary small mb-4">Ek notları giriniz.</p>
            <div class="row g-4">
                <div class="col-12">
                    <x-form.textarea name="notlar" label="Notlar" :value="old('notlar', $personnel?->notlar)" placeholder="Serbest metin" :rows="4" />
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
<script>
document.addEventListener('alpine:init', () => {
    const cities = @json($cities);
    const districts = @json($districts);
    const oldCountryId = '{{ old("country_id", $personnel?->country_id) }}';
    const oldCityId = '{{ old("city_id", $personnel?->city_id) }}';
    const oldDistrictId = '{{ old("district_id", $personnel?->district_id) }}';

    Alpine.data('personnelAddressCascade', () => {
        let selectedCountryId = oldCountryId;
        if (!selectedCountryId && oldCityId) {
            const city = cities.find(c => String(c.id) === String(oldCityId));
            if (city) selectedCountryId = String(city.country_id);
        }
        return {
            cities,
            districts,
            selectedCountryId,
            currentCityId: oldCityId,
            currentDistrictId: oldDistrictId,
            get filteredCities() {
                if (!this.selectedCountryId) return [];
                return this.cities.filter(c => String(c.country_id) === String(this.selectedCountryId));
            },
            get filteredDistricts() {
                if (!this.currentCityId) return [];
                return this.districts.filter(d => String(d.city_id) === String(this.currentCityId));
            },
            init() {},
            onCountryChange(event) {
                this.selectedCountryId = event.target.value || '';
                this.currentCityId = '';
                this.currentDistrictId = '';
            },
            onCityChange(event) {
                this.currentCityId = event.target.value || '';
                this.currentDistrictId = '';
            }
        };
    });
});
</script>
@endpush
