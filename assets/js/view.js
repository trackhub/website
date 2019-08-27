document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('elevation-container');
    const ctx = document.getElementById('elevation').getContext('2d');
    const myChart = new window.chartJs(ctx, {
        type: 'line',
        data: {
            labels: JSON.parse(container.dataset.labels),
            datasets: JSON.parse(container.dataset.datasets),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: false
                    }
                }]
            },
            tooltips: {
                mode: 'nearest'
            }
        }
    });
});