import Chart from 'chart.js/auto';

/**
 * Initialize revenue trend chart
 */
export function initRevenueChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Revenue (TRY)',
                data: data.values,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('tr-TR') + ' ₺';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize order status distribution pie chart
 */
export function initOrderStatusChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    'rgb(59, 130, 246)',   // pending - blue
                    'rgb(245, 158, 11)',   // planned - amber
                    'rgb(16, 185, 129)',   // delivered - green
                    'rgb(239, 68, 68)',    // cancelled - red
                    'rgb(107, 114, 128)',  // other - gray
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize vehicle utilization bar chart
 */
export function initVehicleUtilizationChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Utilization (%)',
                data: data.values,
                backgroundColor: 'rgb(16, 185, 129)',
                borderColor: 'rgb(5, 150, 105)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Utilization: ${context.parsed.y}%`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize delivery performance gauge chart
 */
export function initDeliveryPerformanceChart(canvasId, percentage) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [percentage, 100 - percentage],
                backgroundColor: [
                    percentage >= 90 ? 'rgb(16, 185, 129)' : // green
                    percentage >= 70 ? 'rgb(245, 158, 11)' : // amber
                    'rgb(239, 68, 68)', // red
                    'rgb(229, 231, 235)' // gray for remaining
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            circumference: 180,
            rotation: 270,
            cutout: '75%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            }
        },
        plugins: [{
            id: 'gaugeText',
            afterDraw: function(chart) {
                const { width, height, ctx } = chart;
                ctx.restore();
                
                const fontSize = (height / 114).toFixed(2);
                ctx.font = `${fontSize}em sans-serif`;
                ctx.textBaseline = 'middle';
                ctx.fillStyle = '#1f2937';
                
                const text = percentage + '%';
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 1.5;
                
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
    });
}

// Auto-initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if chart data is available in window object
    if (window.chartData) {
        if (window.chartData.revenue) {
            initRevenueChart('revenueChart', window.chartData.revenue);
        }
        if (window.chartData.orderStatus) {
            initOrderStatusChart('orderStatusChart', window.chartData.orderStatus);
        }
        if (window.chartData.vehicleUtilization) {
            initVehicleUtilizationChart('vehicleUtilizationChart', window.chartData.vehicleUtilization);
        }
        if (window.chartData.deliveryPerformance !== undefined) {
            initDeliveryPerformanceChart('deliveryPerformanceChart', window.chartData.deliveryPerformance);
        }
    }
});
