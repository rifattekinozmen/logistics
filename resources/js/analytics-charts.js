import {
    hasValidChartData,
    initRevenueChart,
    initOrderStatusChart,
    initDeliveryPerformanceChart,
    initVehicleUtilizationChart,
    initMonthlyRevenueBarChart,
} from './charts.js';

function showChartPlaceholder(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || canvas.closest('[data-chart-placeholder]')) return;
    const parent = canvas.parentElement;
    if (!parent) return;
    canvas.style.display = 'none';
    const placeholder = document.createElement('div');
    placeholder.setAttribute('data-chart-placeholder', '');
    placeholder.className = 'd-flex align-items-center justify-content-center text-secondary small py-5';
    placeholder.style.minHeight = '200px';
    placeholder.textContent = 'Veri bulunamadı';
    parent.appendChild(placeholder);
}

function isValidDeliveryPerformance(value) {
    return typeof value === 'number' && !Number.isNaN(value) && value >= 0 && value <= 100;
}

document.addEventListener('DOMContentLoaded', function () {
    if (!window.chartData) return;

    try {
        if (window.chartData.revenue) {
            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                if (hasValidChartData(window.chartData.revenue)) {
                    initRevenueChart('revenueChart', window.chartData.revenue);
                } else {
                    showChartPlaceholder('revenueChart');
                }
            }
        }
        if (window.chartData.orderStatus) {
            const ctx = document.getElementById('orderStatusChart');
            if (ctx) {
                if (hasValidChartData(window.chartData.orderStatus)) {
                    initOrderStatusChart('orderStatusChart', window.chartData.orderStatus);
                } else {
                    showChartPlaceholder('orderStatusChart');
                }
            }
        }
        if (window.chartData.deliveryPerformance !== undefined) {
            const ctx = document.getElementById('deliveryPerformanceChart');
            if (ctx) {
                if (isValidDeliveryPerformance(window.chartData.deliveryPerformance)) {
                    initDeliveryPerformanceChart('deliveryPerformanceChart', window.chartData.deliveryPerformance);
                } else {
                    showChartPlaceholder('deliveryPerformanceChart');
                }
            }
        }
        if (window.chartData.vehicleUtilization) {
            const ctx = document.getElementById('vehicleUtilizationChart');
            if (ctx) {
                if (hasValidChartData(window.chartData.vehicleUtilization)) {
                    initVehicleUtilizationChart('vehicleUtilizationChart', window.chartData.vehicleUtilization);
                } else {
                    showChartPlaceholder('vehicleUtilizationChart');
                }
            }
        }
        if (window.chartData.monthlyComparison) {
            const ctx = document.getElementById('monthlyComparisonChart');
            if (ctx) {
                if (hasValidChartData(window.chartData.monthlyComparison)) {
                    initMonthlyRevenueBarChart('monthlyComparisonChart', window.chartData.monthlyComparison);
                } else {
                    showChartPlaceholder('monthlyComparisonChart');
                }
            }
        }
    } catch (err) {
        console.warn('[analytics-charts] Chart initialization failed:', err);
    }
});
