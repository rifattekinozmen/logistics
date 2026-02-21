import Chart from 'chart.js/auto';

/**
 * Check if chart data has valid labels and values for rendering.
 */
export function hasValidChartData(data) {
    return data
        && Array.isArray(data.labels)
        && Array.isArray(data.values)
        && data.labels.length > 0
        && data.values.length > 0;
}

/**
 * Initialize revenue trend chart
 */
export function initRevenueChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    if (!hasValidChartData(data)) return;

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
const defaultStatusColors = [
    'rgb(59, 130, 246)',
    'rgb(245, 158, 11)',
    'rgb(16, 185, 129)',
    'rgb(239, 68, 68)',
    'rgb(107, 114, 128)',
];

export function initOrderStatusChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    if (!hasValidChartData(data)) return;

    const colors = data.colors && data.colors.length === data.values.length
        ? data.colors
        : defaultStatusColors;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: colors,
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
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0';
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize monthly revenue bar chart (for aylık karşılaştırma)
 */
export function initMonthlyRevenueBarChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    if (!hasValidChartData(data)) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Gelir (₺)',
                data: data.values,
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.parsed.x.toLocaleString('tr-TR') + ' ₺';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString('tr-TR') + ' ₺';
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
    if (!hasValidChartData(data)) return;

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
    if (typeof percentage !== 'number' || isNaN(percentage) || percentage < 0 || percentage > 100) return;

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
