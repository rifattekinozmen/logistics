import {
    initRevenueChart,
    initOrderStatusChart,
    initDeliveryPerformanceChart,
    initVehicleUtilizationChart,
} from './charts.js';

document.addEventListener('DOMContentLoaded', function () {
    if (window.chartData) {
        if (window.chartData.revenue) {
            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                initRevenueChart('revenueChart', window.chartData.revenue);
            }
        }
        if (window.chartData.orderStatus) {
            const ctx = document.getElementById('orderStatusChart');
            if (ctx) {
                initOrderStatusChart('orderStatusChart', window.chartData.orderStatus);
            }
        }
        if (window.chartData.deliveryPerformance !== undefined) {
            const ctx = document.getElementById('deliveryPerformanceChart');
            if (ctx) {
                initDeliveryPerformanceChart('deliveryPerformanceChart', window.chartData.deliveryPerformance);
            }
        }
        if (window.chartData.vehicleUtilization) {
            const ctx = document.getElementById('vehicleUtilizationChart');
            if (ctx) {
                initVehicleUtilizationChart('vehicleUtilizationChart', window.chartData.vehicleUtilization);
            }
        }
    }
});
