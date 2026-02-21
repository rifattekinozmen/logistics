@extends('layouts.app')

@section('title', 'Filo Performansı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Filo Performansı</h2>
        <p class="text-secondary mb-0">Araç kullanımı, bakım ve verimlilik metrikleri</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Toplam Araç</span>
                <span class="material-symbols-outlined text-primary">local_shipping</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">{{ $performance['total_vehicles'] }}</h3>
            <p class="text-secondary small mb-0">Aktif filo</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Görevdeki Araç</span>
                <span class="material-symbols-outlined text-success">drive_eta</span>
            </div>
            <h3 class="h2 fw-bold text-success mb-1">{{ $performance['active_vehicles'] }}</h3>
            <p class="text-secondary small mb-0">{{ number_format($performance['utilization_rate'], 1) }}% kullanım</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Bakım Bekleyen</span>
                <span class="material-symbols-outlined text-warning">build</span>
            </div>
            <h3 class="h2 fw-bold text-warning mb-1">{{ $performance['maintenance_due'] }}</h3>
            <p class="text-secondary small mb-0">Yaklaşan bakım</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Ort. Yakıt Verimliliği</span>
                <span class="material-symbols-outlined text-info">local_gas_station</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">{{ number_format($performance['avg_fuel_efficiency'], 1) }}</h3>
            <p class="text-secondary small mb-0">L/100km</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Araç Kullanım Oranları</h3>
            <div style="height: 350px;">
                <canvas id="vehicleUtilizationChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Araç Durumu</h3>
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-secondary">Aktif</span>
                    <span class="fw-bold">{{ $performance['active_vehicles'] }}</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" 
                         style="width: {{ $performance['total_vehicles'] > 0 ? ($performance['active_vehicles'] / $performance['total_vehicles'] * 100) : 0 }}%">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-secondary">Boşta</span>
                    <span class="fw-bold">{{ $performance['idle_vehicles'] }}</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-info" 
                         style="width: {{ $performance['total_vehicles'] > 0 ? ($performance['idle_vehicles'] / $performance['total_vehicles'] * 100) : 0 }}%">
                    </div>
                </div>
            </div>
            <div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-secondary">Bakımda</span>
                    <span class="fw-bold">{{ $performance['maintenance_due'] }}</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-warning" 
                         style="width: {{ $performance['total_vehicles'] > 0 ? ($performance['maintenance_due'] / $performance['total_vehicles'] * 100) : 0 }}%">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <div class="col-12">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Bakım Uyarıları</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="small fw-semibold text-secondary">Araç</th>
                            <th class="small fw-semibold text-secondary">Plaka</th>
                            <th class="small fw-semibold text-secondary">Son Bakım</th>
                            <th class="small fw-semibold text-secondary">Kilometre</th>
                            <th class="small fw-semibold text-secondary text-end">Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($performance['maintenance_alerts'] as $alert)
                        <tr>
                            <td class="fw-semibold">{{ $alert['name'] }}</td>
                            <td><span class="font-monospace">{{ $alert['plate_number'] }}</span></td>
                            <td class="small">{{ $alert['last_maintenance'] }}</td>
                            <td class="small">{{ number_format($alert['current_km']) }} km</td>
                            <td class="text-end">
                                <span class="badge bg-{{ $alert['urgency_color'] }} bg-opacity-10 text-{{ $alert['urgency_color'] }} px-3 py-2 rounded-pill">
                                    {{ $alert['urgency_label'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                <span class="material-symbols-outlined d-block mb-2" style="font-size: 3rem;">check_circle</span>
                                Tüm araçlar bakım planında
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.chartData = {
    vehicleUtilization: {
        labels: @json(array_column($performance['vehicle_utilization'], 'name')),
        values: @json(array_column($performance['vehicle_utilization'], 'utilization'))
    }
};
</script>
@vite(['resources/js/analytics-charts.js'])
@endpush
@endsection
