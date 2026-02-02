@extends('layouts.app')

@section('title', 'Müşteri Düzenle - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Müşteri Düzenle</h2>
        <p class="text-secondary mb-0">Müşteri bilgilerini güncelleyin</p>
    </div>
    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-info-200);">
    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Müşteri Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('name') is-invalid border-danger @enderror" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">E-posta</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('email') is-invalid border-danger @enderror">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('phone') is-invalid border-danger @enderror">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Vergi Numarası</label>
                <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}" class="form-control border-info-200 focus:border-info focus:ring-info @error('tax_number') is-invalid border-danger @enderror">
                @error('tax_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-dark">Adres</label>
                <textarea name="address" class="form-control border-info-200 focus:border-info focus:ring-info @error('address') is-invalid border-danger @enderror" rows="3">{{ old('address', $customer->address) }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark">Durum <span class="text-danger">*</span></label>
                <select name="status" class="form-select border-info-200 focus:border-info focus:ring-info @error('status') is-invalid border-danger @enderror" required>
                    <option value="1" {{ old('status', $customer->status) == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status', $customer->status) == '0' ? 'selected' : '' }}>Pasif</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-4 border-top" style="border-color: var(--bs-info-200);">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn bg-secondary-200 text-secondary border-0 hover:bg-secondary hover:text-white transition-all">İptal</a>
            <button type="submit" class="btn btn-primary shadow-sm hover:shadow-md transition-all">Güncelle</button>
        </div>
    </form>
</div>

{{-- Favori / Teslimat Adresleri --}}
<div id="favorite-addresses" class="bg-white rounded-3xl shadow-sm border p-4 mt-4" style="border-color: var(--bs-customers-200);">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="h4 fw-bold text-dark mb-0">Favori / Teslimat Adresleri</h3>
        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addFavoriteAddressModal">
            <span class="material-symbols-outlined" style="font-size: 1rem;">add</span>
            Yeni Adres Ekle
        </button>
    </div>
    @if($customer->favoriteAddresses->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-primary-200">
                    <tr>
                        <th class="border-0 fw-semibold text-secondary small">Ad / Tür</th>
                        <th class="border-0 fw-semibold text-secondary small">Adres</th>
                        <th class="border-0 fw-semibold text-secondary small">Enlem / Boylam</th>
                        <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->favoriteAddresses as $addr)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $addr->name }}</span>
                            <span class="badge bg-primary-200 text-primary rounded-pill px-2 py-1 small ms-1">{{ match($addr->type) { 'pickup' => 'Alış', 'delivery' => 'Teslimat', 'both' => 'Her İkisi', default => $addr->type } }}</span>
                        </td>
                        <td class="align-middle small text-secondary">{{ Str::limit($addr->address, 50) }}</td>
                        <td class="align-middle small text-secondary">
                            @if($addr->latitude !== null && $addr->longitude !== null)
                                {{ number_format((float) $addr->latitude, 6) }}, {{ number_format((float) $addr->longitude, 6) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="align-middle text-end">
                            <button type="button" class="btn btn-sm bg-primary-200 text-primary border-0" title="Düzenle" data-bs-toggle="modal" data-bs-target="#editFavoriteAddressModal" data-addr-update-url="{{ route('admin.customers.favorite-addresses.update', [$customer, $addr]) }}" data-addr-name="{{ e($addr->name) }}" data-addr-type="{{ $addr->type }}" data-addr-address="{{ e($addr->address) }}" data-addr-lat="{{ $addr->latitude }}" data-addr-lon="{{ $addr->longitude }}" data-addr-contact-name="{{ e($addr->contact_name ?? '') }}" data-addr-contact-phone="{{ e($addr->contact_phone ?? '') }}" data-addr-notes="{{ e($addr->notes ?? '') }}" data-addr-sort-order="{{ $addr->sort_order }}">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </button>
                            <form action="{{ route('admin.customers.favorite-addresses.destroy', [$customer, $addr]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu adresi silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0" title="Sil">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-secondary mb-0 small">Henüz favori veya teslimat adresi yok. "Yeni Adres Ekle" ile ekleyebilirsiniz.</p>
    @endif
</div>

{{-- Modal: Yeni Favori Adres --}}
<div class="modal fade" id="addFavoriteAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.customers.favorite-addresses.store', $customer) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Favori / Teslimat Adresi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="fa_name_select" class="form-label fw-semibold text-dark">Adres Adı <span class="text-danger">*</span></label>
                            <select id="fa_name_select" class="form-select">
                                <option value="Ev">Ev</option>
                                <option value="İş">İş</option>
                                <option value="Ofis">Ofis</option>
                                <option value="Depo">Depo</option>
                                <option value="Fabrika">Fabrika</option>
                                <option value="Şube">Şube</option>
                                <option value="Showroom">Showroom</option>
                                <option value="">Diğer</option>
                            </select>
                            <input type="hidden" name="name" id="fa_name">
                            <div id="fa_name_other_wrap" class="mt-2 d-none">
                                <label for="fa_name_other" class="form-label small text-secondary">Diğer (yazın)</label>
                                <input type="text" id="fa_name_other" class="form-control" placeholder="Adres adını yazın" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="fa_type" class="form-label fw-semibold text-dark">Tür <span class="text-danger">*</span></label>
                            <select name="type" id="fa_type" class="form-select" required>
                                <option value="pickup">Alış Adresi</option>
                                <option value="delivery">Teslimat Adresi</option>
                                <option value="both">Her İkisi</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="fa_address" class="form-label fw-semibold text-dark">Adres <span class="text-danger">*</span></label>
                            <textarea name="address" id="fa_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Koordinatlar</label>
                            <input type="text" id="fa_coordinates_paste" class="form-control" placeholder="Google Maps'ten kopyalayıp buraya yapıştırın (enlem, boylam)" autocomplete="off">
                            <small class="text-secondary">Örn: 39.66127977158162, 30.728054956227</small>
                        </div>
                        <div class="col-md-6">
                            <label for="fa_latitude" class="form-label fw-semibold text-dark">Enlem</label>
                            <input type="text" name="latitude" id="fa_latitude" class="form-control" placeholder="Örn: 41.0082" inputmode="decimal">
                        </div>
                        <div class="col-md-6">
                            <label for="fa_longitude" class="form-label fw-semibold text-dark">Boylam</label>
                            <input type="text" name="longitude" id="fa_longitude" class="form-control" placeholder="Örn: 28.9784" inputmode="decimal">
                        </div>
                        <div class="col-md-6">
                            <label for="fa_contact_name" class="form-label fw-semibold text-dark">İletişim Adı</label>
                            <input type="text" name="contact_name" id="fa_contact_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="fa_contact_phone" class="form-label fw-semibold text-dark">İletişim Telefonu</label>
                            <input type="text" name="contact_phone" id="fa_contact_phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="fa_sort_order" class="form-label fw-semibold text-dark">Sıra</label>
                            <input type="number" name="sort_order" id="fa_sort_order" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-12">
                            <label for="fa_notes" class="form-label fw-semibold text-dark">Notlar</label>
                            <textarea name="notes" id="fa_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Favori Adres Düzenle --}}
<div class="modal fade" id="editFavoriteAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editFavoriteAddressForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Favori Adresi Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="fe_name_select" class="form-label fw-semibold text-dark">Adres Adı <span class="text-danger">*</span></label>
                            <select id="fe_name_select" class="form-select">
                                <option value="Ev">Ev</option>
                                <option value="İş">İş</option>
                                <option value="Ofis">Ofis</option>
                                <option value="Depo">Depo</option>
                                <option value="Fabrika">Fabrika</option>
                                <option value="Şube">Şube</option>
                                <option value="Showroom">Showroom</option>
                                <option value="">Diğer</option>
                            </select>
                            <input type="hidden" name="name" id="fe_name">
                            <div id="fe_name_other_wrap" class="mt-2 d-none">
                                <label for="fe_name_other" class="form-label small text-secondary">Diğer (yazın)</label>
                                <input type="text" id="fe_name_other" class="form-control" placeholder="Adres adını yazın" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="fe_type" class="form-label fw-semibold text-dark">Tür <span class="text-danger">*</span></label>
                            <select name="type" id="fe_type" class="form-select" required>
                                <option value="pickup">Alış Adresi</option>
                                <option value="delivery">Teslimat Adresi</option>
                                <option value="both">Her İkisi</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="fe_address" class="form-label fw-semibold text-dark">Adres <span class="text-danger">*</span></label>
                            <textarea name="address" id="fe_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Koordinatlar</label>
                            <input type="text" id="fe_coordinates_paste" class="form-control" placeholder="Google Maps'ten kopyalayıp buraya yapıştırın (enlem, boylam)" autocomplete="off">
                            <small class="text-secondary">Örn: 39.66127977158162, 30.728054956227</small>
                        </div>
                        <div class="col-md-6">
                            <label for="fe_latitude" class="form-label fw-semibold text-dark">Enlem</label>
                            <input type="text" name="latitude" id="fe_latitude" class="form-control" inputmode="decimal">
                        </div>
                        <div class="col-md-6">
                            <label for="fe_longitude" class="form-label fw-semibold text-dark">Boylam</label>
                            <input type="text" name="longitude" id="fe_longitude" class="form-control" inputmode="decimal">
                        </div>
                        <div class="col-md-6">
                            <label for="fe_contact_name" class="form-label fw-semibold text-dark">İletişim Adı</label>
                            <input type="text" name="contact_name" id="fe_contact_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="fe_contact_phone" class="form-label fw-semibold text-dark">İletişim Telefonu</label>
                            <input type="text" name="contact_phone" id="fe_contact_phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="fe_sort_order" class="form-label fw-semibold text-dark">Sıra</label>
                            <input type="number" name="sort_order" id="fe_sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-12">
                            <label for="fe_notes" class="form-label fw-semibold text-dark">Notlar</label>
                            <textarea name="notes" id="fe_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function parseLatLng(text) {
        if (!text || typeof text !== 'string') return null;
        var t = text.trim().replace(/\s+/g, ' ');
        var m = t.match(/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/);
        return m ? [m[1], m[2]] : null;
    }
    var geocodeReverseUrl = '{{ route("geocode.reverse") }}';
    function attachPasteSplit(latInput, lonInput, pasteInput, addressInput) {
        if (!latInput || !lonInput) return;
        function fetchAddressForCoords(lat, lon) {
            if (!addressInput || !geocodeReverseUrl) return;
            fetch(geocodeReverseUrl + '?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon), {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data && data.address) addressInput.value = data.address;
            }).catch(function () {});
        }
        function trySplit(val) {
            var pair = parseLatLng(val);
            if (pair) {
                latInput.value = pair[0];
                lonInput.value = pair[1];
                if (pasteInput) pasteInput.value = '';
                fetchAddressForCoords(pair[0], pair[1]);
                return true;
            }
            return false;
        }
        if (pasteInput) {
            pasteInput.addEventListener('paste', function (e) {
                var text = (e.clipboardData || window.clipboardData).getData('text');
                if (trySplit(text)) e.preventDefault();
            });
            pasteInput.addEventListener('input', function () {
                trySplit(pasteInput.value);
            });
        }
        latInput.addEventListener('paste', function (e) {
            var text = (e.clipboardData || window.clipboardData).getData('text');
            if (trySplit(text)) e.preventDefault();
        });
    }
    attachPasteSplit(document.getElementById('fa_latitude'), document.getElementById('fa_longitude'), document.getElementById('fa_coordinates_paste'), document.getElementById('fa_address'));
    attachPasteSplit(document.getElementById('fe_latitude'), document.getElementById('fe_longitude'), document.getElementById('fe_coordinates_paste'), document.getElementById('fe_address'));

    var addressNameOptions = ['Ev', 'İş', 'Ofis', 'Depo', 'Fabrika', 'Şube', 'Showroom'];
    function setupAddressNameSelect(selectId, hiddenId, otherWrapId, otherId) {
        var sel = document.getElementById(selectId);
        var hid = document.getElementById(hiddenId);
        var wrap = document.getElementById(otherWrapId);
        var other = document.getElementById(otherId);
        if (!sel || !hid) return;
        function sync() {
            if (sel.value === '') {
                if (wrap) wrap.classList.remove('d-none');
                hid.value = other ? other.value.trim() : '';
            } else {
                if (wrap) wrap.classList.add('d-none');
                hid.value = sel.value;
            }
        }
        sel.addEventListener('change', sync);
        if (other) other.addEventListener('input', function () { hid.value = this.value.trim(); });
        sync();
    }
    setupAddressNameSelect('fa_name_select', 'fa_name', 'fa_name_other_wrap', 'fa_name_other');
    setupAddressNameSelect('fe_name_select', 'fe_name', 'fe_name_other_wrap', 'fe_name_other');
    var faForm = document.querySelector('#addFavoriteAddressModal form');
    if (faForm) faForm.addEventListener('submit', function () {
        var s = document.getElementById('fa_name_select');
        var h = document.getElementById('fa_name');
        var o = document.getElementById('fa_name_other');
        if (s && s.value === '' && o && h) h.value = o.value.trim();
    });

    var editModal = document.getElementById('editFavoriteAddressModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            if (!btn || !btn.dataset.addrUpdateUrl) return;
            document.getElementById('editFavoriteAddressForm').action = btn.dataset.addrUpdateUrl;
            var addrName = btn.dataset.addrName || '';
            var feSel = document.getElementById('fe_name_select');
            var feOther = document.getElementById('fe_name_other');
            var feWrap = document.getElementById('fe_name_other_wrap');
            if (feSel) {
                if (['Ev', 'İş', 'Ofis', 'Depo', 'Fabrika', 'Şube', 'Showroom'].indexOf(addrName) >= 0) {
                    feSel.value = addrName;
                    if (feWrap) feWrap.classList.add('d-none');
                    if (feOther) feOther.value = '';
                } else {
                    feSel.value = '';
                    if (feWrap) feWrap.classList.remove('d-none');
                    if (feOther) feOther.value = addrName;
                }
            }
            document.getElementById('fe_name').value = addrName;
            document.getElementById('fe_type').value = btn.dataset.addrType || 'delivery';
            document.getElementById('fe_address').value = btn.dataset.addrAddress || '';
            document.getElementById('fe_latitude').value = btn.dataset.addrLat || '';
            document.getElementById('fe_longitude').value = btn.dataset.addrLon || '';
            document.getElementById('fe_contact_name').value = btn.dataset.addrContactName || '';
            document.getElementById('fe_contact_phone').value = btn.dataset.addrContactPhone || '';
            document.getElementById('fe_notes').value = btn.dataset.addrNotes || '';
            document.getElementById('fe_sort_order').value = btn.dataset.addrSortOrder || '0';
            var fePaste = document.getElementById('fe_coordinates_paste');
            if (fePaste) fePaste.value = '';
        });
    }
});
</script>
@endpush
@endsection
