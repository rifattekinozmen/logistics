@extends('layouts.customer-app')

@section('title', 'Favori Adreslerim - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">location_on</span>
            <h2 class="h3 fw-bold text-dark mb-0">Favori Adreslerim</h2>
        </div>
        <p class="text-secondary mb-0">Sık kullandığınız adresleri kaydedin ve hızlıca seçin</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @php $addressesWithCoords = $addresses->filter(fn($a) => $a->latitude !== null && $a->longitude !== null); @endphp
        @if($addressesWithCoords->isNotEmpty())
            <button type="button" id="btnShowMap" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">map</span>
                Haritada Görüntüle
            </button>
        @endif
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Adres Ekle
        </button>
    </div>
</div>

@if($addressesWithCoords->isNotEmpty())
<div id="mapContainer" class="bg-white rounded-3xl shadow-sm border overflow-hidden mb-4" style="display: none;">
    <div id="addressMap" style="height: 400px;"></div>
</div>
<script>
window.FAVORITE_ADDRESSES_MAP_DATA = @json($addressesWithCoords->map(function ($a) { return ['name' => $a->name, 'lat' => (float) $a->latitude, 'lng' => (float) $a->longitude]; })->values());
</script>
@endif

<div class="row g-4">
    @forelse($addresses as $address)
        <div class="col-md-6">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="grow">
                        <h5 class="fw-bold text-dark mb-1">{{ $address->name }}</h5>
                        <span class="badge bg-primary-200 text-primary rounded-pill px-3 py-1">
                            {{ match($address->type) { 'pickup' => 'Alış', 'delivery' => 'Teslimat', 'both' => 'Her İkisi', default => $address->type } }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('customer.favorite-addresses.destroy', $address) }}" class="d-inline" onsubmit="return confirm('Bu adresi silmek istediğinizden emin misiniz?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                        </button>
                    </form>
                </div>
                <p class="text-secondary mb-2">{{ $address->address }}</p>
                @if($address->contact_name || $address->contact_phone)
                    <div class="small text-secondary mb-2">
                        @if($address->contact_name)
                            <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">person</span>
                            {{ $address->contact_name }}
                        @endif
                        @if($address->contact_phone)
                            <span class="material-symbols-outlined align-middle ms-2" style="font-size: 0.875rem;">phone</span>
                            {{ $address->contact_phone }}
                        @endif
                    </div>
                @endif
                @if($address->notes)
                    <p class="small text-secondary mb-0">{{ $address->notes }}</p>
                @endif
                @if($address->latitude !== null && $address->longitude !== null)
                    <p class="small text-secondary mb-0 mt-2">
                        <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">location_on</span>
                        {{ number_format((float) $address->latitude, 6) }}, {{ number_format((float) $address->longitude, 6) }}
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <a href="https://www.google.com/maps?q={{ (float) $address->latitude }},{{ (float) $address->longitude }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                            <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">map</span>
                            Google Maps
                        </a>
                        <a href="https://www.openstreetmap.org/?mlat={{ (float) $address->latitude }}&mlon={{ (float) $address->longitude }}&zoom=17" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                            OpenStreetMap
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="bg-white rounded-3xl shadow-sm border p-5 text-center">
                <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">location_off</span>
                <p class="text-secondary mb-3">Henüz favori adres eklenmemiş.</p>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">add</span>
                    İlk Adresi Ekle
                </button>
            </div>
        </div>
    @endforelse
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customer.favorite-addresses.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Favori Adres Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name_select" class="form-label fw-semibold text-dark">Adres Adı <span class="text-danger">*</span></label>
                        <select id="name_select" class="form-select">
                            <option value="Ev">Ev</option>
                            <option value="İş">İş</option>
                            <option value="Ofis">Ofis</option>
                            <option value="Depo">Depo</option>
                            <option value="Fabrika">Fabrika</option>
                            <option value="Şube">Şube</option>
                            <option value="Showroom">Showroom</option>
                            <option value="">Diğer</option>
                        </select>
                        <input type="hidden" name="name" id="name" required>
                        <div id="name_other_wrap" class="mt-2 d-none">
                            <label for="name_other" class="form-label small text-secondary">Diğer (yazın)</label>
                            <input type="text" id="name_other" class="form-control" placeholder="Adres adını yazın" maxlength="255">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label fw-semibold text-dark">Adres Türü <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="pickup">Alış Adresi</option>
                            <option value="delivery">Teslimat Adresi</option>
                            <option value="both">Her İkisi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold text-dark">Adres <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 mb-2">
                            <textarea name="address" id="address" class="form-control flex-grow-1" rows="3" required></textarea>
                            <button type="button" id="btnGeocode" class="btn btn-outline-primary align-self-start" title="Adresten enlem/boylam getir">
                                <span class="material-symbols-outlined">my_location</span>
                            </button>
                        </div>
                        <small class="text-secondary">İsteğe bağlı: "Konum getir" ile adresin koordinatlarını doldurabilirsiniz.</small>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Koordinatlar</label>
                            <input type="text" id="coordinates-paste" class="form-control" placeholder="Google Maps'ten kopyalayıp buraya yapıştırın (enlem, boylam)" autocomplete="off">
                            <small class="text-secondary">Örn: 39.66127977158162, 30.728054956227</small>
                        </div>
                        <div class="col-md-6">
                            <label for="latitude" class="form-label fw-semibold text-dark">Enlem</label>
                            <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Örn: 41.0082" inputmode="decimal">
                        </div>
                        <div class="col-md-6">
                            <label for="longitude" class="form-label fw-semibold text-dark">Boylam</label>
                            <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Örn: 28.9784" inputmode="decimal">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contact_name" class="form-label fw-semibold text-dark">İletişim Adı</label>
                            <input type="text" name="contact_name" id="contact_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label fw-semibold text-dark">İletişim Telefonu</label>
                            <input type="text" name="contact_phone" id="contact_phone" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="notes" class="form-label fw-semibold text-dark">Notlar</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnGeocode');
    var addressEl = document.getElementById('address');
    var latEl = document.getElementById('latitude');
    var lonEl = document.getElementById('longitude');
    var pasteEl = document.getElementById('coordinates-paste');

    function parseLatLng(text) {
        if (!text || typeof text !== 'string') return null;
        var t = text.trim().replace(/\s+/g, ' ');
        var m = t.match(/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/);
        return m ? [m[1], m[2]] : null;
    }

    function attachPasteSplit(latInput, lonInput, pasteInput, addressInput, reverseUrl) {
        if (!latInput || !lonInput) return;
        function fetchAddressForCoords(lat, lon) {
            if (!addressInput || !reverseUrl) return;
            fetch(reverseUrl + '?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon), {
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

    attachPasteSplit(latEl, lonEl, pasteEl, addressEl, '{{ route("geocode.reverse") }}');

    var nameSelect = document.getElementById('name_select');
    var nameHidden = document.getElementById('name');
    var nameOtherWrap = document.getElementById('name_other_wrap');
    var nameOther = document.getElementById('name_other');
    if (nameSelect && nameHidden) {
        function syncName() {
            if (nameSelect.value === '') {
                nameOtherWrap.classList.remove('d-none');
                nameHidden.value = nameOther ? nameOther.value.trim() : '';
            } else {
                nameOtherWrap.classList.add('d-none');
                nameHidden.value = nameSelect.value;
            }
        }
        nameSelect.addEventListener('change', syncName);
        if (nameOther) nameOther.addEventListener('input', function () { nameHidden.value = this.value.trim(); });
        syncName();
        document.getElementById('addAddressModal').querySelector('form').addEventListener('submit', function () {
            if (nameSelect.value === '' && nameOther) nameHidden.value = nameOther.value.trim();
        });
    }

    if (!btn || !addressEl || !latEl || !lonEl) return;

    btn.addEventListener('click', function () {
        var address = addressEl.value.trim();
        if (!address) {
            alert('Önce adres alanını doldurun.');
            return;
        }
        btn.disabled = true;
        btn.querySelector('.material-symbols-outlined').textContent = 'hourglass_empty';
        fetch('{{ route("customer.favorite-addresses.geocode") }}?address=' + encodeURIComponent(address), {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.latitude != null && data.longitude != null) {
                    latEl.value = data.latitude;
                    lonEl.value = data.longitude;
                } else {
                    alert(data.message || 'Konum bulunamadı.');
                }
            })
            .catch(function () {
                alert('Konum getirilirken bir hata oluştu.');
            })
            .finally(function () {
                btn.disabled = false;
                btn.querySelector('.material-symbols-outlined').textContent = 'my_location';
            });
    });

    var btnShowMap = document.getElementById('btnShowMap');
    var mapContainer = document.getElementById('mapContainer');
    var mapInitialized = false;
    if (btnShowMap && mapContainer && typeof window.FAVORITE_ADDRESSES_MAP_DATA !== 'undefined' && window.FAVORITE_ADDRESSES_MAP_DATA.length) {
        btnShowMap.addEventListener('click', function () {
            mapContainer.style.display = mapContainer.style.display === 'none' ? 'block' : 'none';
            if (mapContainer.style.display === 'block' && !mapInitialized) {
                mapInitialized = true;
                var linkCss = document.createElement('link');
                linkCss.rel = 'stylesheet';
                linkCss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                linkCss.crossOrigin = '';
                document.head.appendChild(linkCss);
                var script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.crossOrigin = '';
                script.onload = function () {
                    var L = window.L;
                    var data = window.FAVORITE_ADDRESSES_MAP_DATA;
                    var center = data[0];
                    var map = L.map('addressMap').setView([center.lat, center.lng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                    var bounds = [];
                    data.forEach(function (p) {
                        bounds.push([p.lat, p.lng]);
                        L.marker([p.lat, p.lng]).addTo(map).bindPopup(p.name || 'Adres');
                    });
                    if (data.length > 1) map.fitBounds(bounds, { padding: [30, 30] });
                };
                document.head.appendChild(script);
            }
        });
    }
});
</script>
@endpush
@endsection
