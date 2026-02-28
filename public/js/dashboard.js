(() => {
    if (window.__dashboardChartsBooted) {
        return;
    }
    window.__dashboardChartsBooted = true;

    const state = {
        pieChart: null,
        lineChart: null,
        initQueued: false,
    };

    const destroyChart = (chartKey) => {
        const chart = state[chartKey];
        if (!chart) {
            return;
        }

        try {
            chart.destroy();
        } catch (e) {
            console.error('Error destroying chart:', e);
        } finally {
            state[chartKey] = null;
        }
    };

    const initCharts = () => {
        const pieChartEl = document.querySelector('#loanStatusChart');
        const lineChartEl = document.querySelector('#lendingInsightsChart');

        if (!pieChartEl) {
            destroyChart('pieChart');
        }

        if (!lineChartEl) {
            destroyChart('lineChart');
        }

        if (pieChartEl) {
            try {
                const pieData = JSON.parse(pieChartEl.getAttribute('data-chart') || '{}');

                destroyChart('pieChart');
                pieChartEl.innerHTML = '';

                const pieOptions = {
                    series: pieData.data || [],
                    labels: pieData.labels || [],
                    chart: {
                        id: 'loan-status-chart',
                        type: 'pie',
                        height: 300,
                    },
                    colors: ['#10B981', '#F59E0B', '#3B82F6', '#EF4444'],
                    legend: {
                        position: 'bottom',
                    },
                };

                state.pieChart = new ApexCharts(pieChartEl, pieOptions);
                state.pieChart.render();
            } catch (e) {
                console.error('Error initializing Pie Chart:', e);
            }
        }

        if (lineChartEl) {
            try {
                const lineData = JSON.parse(lineChartEl.getAttribute('data-chart') || '{}');

                destroyChart('lineChart');
                lineChartEl.innerHTML = '';

                const lineOptions = {
                    series: lineData.series || [],
                    chart: {
                        id: 'lending-insights-chart',
                        height: 300,
                        type: 'line',
                        zoom: { enabled: false },
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth' },
                    xaxis: {
                        categories: lineData.labels || [],
                    },
                    colors: ['#3B82F6', '#10B981'],
                };

                state.lineChart = new ApexCharts(lineChartEl, lineOptions);
                state.lineChart.render();
            } catch (e) {
                console.error('Error initializing Line Chart:', e);
            }
        }
    };

    const queueInitCharts = () => {
        if (state.initQueued) {
            return;
        }

        state.initQueued = true;

        requestAnimationFrame(() => {
            state.initQueued = false;
            initCharts();
        });
    };

    document.addEventListener('livewire:initialized', queueInitCharts);
    document.addEventListener('livewire:navigated', queueInitCharts);

    document.addEventListener('livewire:initialized', () => {
        if (typeof Livewire !== 'undefined' && typeof Livewire.on === 'function') {
            Livewire.on('dashboard-refresh-charts', queueInitCharts);
        }
    });
})();
