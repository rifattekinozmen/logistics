@extends('layouts.app')

@section('title', 'Filo Harita - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Filo Harita</h2>
        <p class="text-secondary mb-0">Araçların son GPS konumları (Faz 3)</p>
    </div>
    <a href="{{ route('admin.analytics.fleet') }}" class="btn btn-outline-primary btn-sm">
        <span class="material-symbols-outlined align-middle me-1" style="font-size: 1rem;">bar_chart</span>
        Filo Performansı
    </a>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Son Konumlar</h3>
            <div id="fleet-map-positions-loading" class="text-center py-4 text-secondary">
                <span class="material-symbols-outlined mb-2" style="font-size: 2rem;">progress_activity</span>
                <p class="mb-0 small">Yükleniyor…</p>
            </div>
            <div id="fleet-map-positions-table" class="table-responsive d-none">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="small fw-semibold text-secondary">Plaka</th>
                            <th class="small fw-semibold text-secondary">Enlem</th>
                            <th class="small fw-semibold text-secondary">Boylam</th>
                            <th class="small fw-semibold text-secondary">Kayıt Zamanı</th>
                            <th class="small fw-semibold text-secondary">Kaynak</th>
                        </tr>
                    </thead>
                    <tbody id="fleet-map-positions-tbody"></tbody>
                </table>
            </div>
            <div id="fleet-map-positions-empty" class="text-center py-5 d-none">
                <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 3rem;">location_off</span>
                <p class="text-secondary mb-2">Henüz GPS konumu kaydı yok.</p>
                <p class="small text-secondary mb-0">
                    Cihaz veya sürücü uygulamasından konum gönderildiğinde burada listelenecektir.
                    <br>
                    <a href="{{ url('docs/architecture/07-gps-and-fleet-map.md') }}" class="text-primary" target="_blank" rel="noopener">Mimari doküman</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const loading = document.getElementById('fleet-map-positions-loading');
    const tableWrap = document.getElementById('fleet-map-positions-table');
    const tbody = document.getElementById('fleet-map-positions-tbody');
    const empty = document.getElementById('fleet-map-positions-empty');

    fetch('{{ route('admin.analytics.fleet-map.positions') }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('d-none');
            const items = data.data || [];
            if (items.length === 0) {
                empty.classList.remove('d-none');
                return;
            }
            tableWrap.classList.remove('d-none');
            const sourceLabels = { device: 'Cihaz', driver_app: 'Sürücü Uyg.', manual: 'Manuel' };
            items.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML =
                    '<td class="fw-semibold font-monospace">' + (p.plate || '–') + '</td>' +
                    '<td class="small">' + Number(p.latitude).toFixed(6) + '</td>' +
                    '<td class="small">' + Number(p.longitude).toFixed(6) + '</td>' +
                    '<td class="small">' + (p.recorded_at || '–') + '</td>' +
                    '<td><span class="badge bg-secondary">' + (sourceLabels[p.source] || p.source || '–') + '</span></td>';
                tbody.appendChild(tr);
            });
        })
        .catch(() => {
            loading.classList.add('d-none');
            empty.classList.remove('d-none');
            empty.querySelector('p.text-secondary').textContent = 'Konumlar yüklenirken hata oluştu.';
        });
})();
</script>
@endpush
