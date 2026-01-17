const initCharts = () => {
    const pieChartEl = document.querySelector("#loanStatusChart");
    const lineChartEl = document.querySelector("#lendingInsightsChart");

    if (pieChartEl && !pieChartEl.chart) {
        try {
            const pieData = JSON.parse(pieChartEl.getAttribute('data-chart'));
            
            const pieOptions = {
                series: pieData.data,
                labels: pieData.labels,
                chart: {
                    type: 'pie',
                    height: 300
                },
                colors: ['#10B981', '#F59E0B', '#3B82F6', '#EF4444'], // Paid(Green), Pending(Yellow), DueSoon(Blue), Late(Red)
                legend: {
                    position: 'bottom'
                }
            };
            const pieChart = new ApexCharts(pieChartEl, pieOptions);
            pieChart.render();
            pieChartEl.chart = pieChart; // Store instance
        } catch (e) {
            console.error('Error initializing Pie Chart:', e);
        }
    }

    if (lineChartEl && !lineChartEl.chart) {
        try {
            const lineData = JSON.parse(lineChartEl.getAttribute('data-chart'));
            
            const lineOptions = {
                series: lineData.series,
                chart: {
                    height: 300,
                    type: 'line',
                    zoom: { enabled: false }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth' },
                xaxis: {
                    categories: lineData.labels,
                },
                colors: ['#3B82F6', '#10B981'], // Lent(Blue), CashIn(Green)
            };
            const lineChart = new ApexCharts(lineChartEl, lineOptions);
            lineChart.render();
            lineChartEl.chart = lineChart; // Store instance
        } catch (e) {
            console.error('Error initializing Line Chart:', e);
        }
    }
};

document.addEventListener('livewire:initialized', initCharts);
document.addEventListener('livewire:navigated', initCharts);
