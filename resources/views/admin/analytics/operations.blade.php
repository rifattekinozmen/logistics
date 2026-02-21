@extends('layouts.app')

@section('title', 'Operasyonel Analiz - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Operasyonel Analiz</h2>
        <p class="text-secondary mb-0">Sipariş, sevkiyat ve teslimat performansı</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Toplam Sipariş</span>
                <span class="material-symbols-outlined text-primary">shopping_cart</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">{{ $kpis['total_orders'] }}</h3>
            <p class="text-secondary small mb-0">Son 30 gün</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Tamamlanan</span>
                <span class="material-symbols-outlined text-success">check_circle</span>
            </div>
            <h3 class="h2 fw-bold text-success mb-1">{{ $kpis['completed_orders'] }}</h3>
            <p class="text-secondary small mb-0">{{ number_format($kpis['completion_rate'], 1) }}% başarı</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Zamanında Teslimat</span>
                <span class="material-symbols-outlined text-info">schedule</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">{{ number_format($kpis['on_time_delivery_rate'], 1) }}%</h3>
            <p class="text-secondary small mb-0">{{ $kpis['on_time_deliveries'] }}/{{ $kpis['total_deliveries'] }} teslimat</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Ort. İşleme Süresi</span>
                <span class="material-symbols-outlined text-warning">timer</span>
            </div>
            <h3 class="h2 fw-bold text-dark mb-1">{{ number_format($kpis['avg_processing_time'], 1) }}</h3>
            <p class="text-secondary small mb-0">Saat</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Sipariş Durumu Dağılımı</h3>
            <div style="height: 300px;">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Teslimat Performansı</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="deliveryPerformanceChart"></canvas>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                    <div class="text-secondary small mb-1">Zamanında Teslimat</div>
                    <div class="h1 fw-bold text-dark">{{ number_format($kpis['on_time_delivery_rate'], 1) }}%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <div class="col-12">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-4">Durum Detayları</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="small fw-semibold text-secondary">Durum</th>
                            <th class="small fw-semibold text-secondary text-end">Adet</th>
                            <th class="small fw-semibold text-secondary text-end">Oran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kpis['status_breakdown'] as $status)
                        <tr>
                            <td>
                                <span class="badge bg-{{ $status['color'] }} bg-opacity-10 text-{{ $status['color'] }} px-3 py-2 rounded-pill">
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold">{{ $status['count'] }}</td>
                            <td class="text-end">{{ number_format($status['percentage'], 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.chartData = {
    orderStatus: {
        labels: @json(array_column($kpis['status_breakdown'], 'label')),
        values: @json(array_column($kpis['status_breakdown'], 'count')),
        colors: @json(array_column($kpis['status_breakdown'], 'chartColor'))
    },
    deliveryPerformance: {{ $kpis['on_time_delivery_rate'] }}
};
</script>
@vite(['resources/js/analytics-charts.js'])
@endpush
@endsection
