document.addEventListener('DOMContentLoaded', function() {
    function renderChart(elementId, options) {
        const el = document.querySelector(`#${elementId}`);
        if (el) {
            const chart = new ApexCharts(el, options);
            chart.render();
        }
    }

    fetch('api/get_report_data.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);

            renderChart('trendChart', {
                chart: { type: 'area', height: 350, toolbar: { show: false } },
                series: [{ name: 'Décès', data: data.trendChart.series }],
                xaxis: { categories: data.trendChart.labels },
                colors: ['#3b82f6'], stroke: { curve: 'smooth' }, dataLabels: { enabled: false }
            });
            
            renderChart('causesChart', {
                chart: { type: 'donut', height: 350 },
                series: data.causesChart.series,
                labels: data.causesChart.labels,
                legend: { position: 'bottom' }
            });
            
            renderChart('zonesChart', {
                chart: { type: 'bar', height: 350 },
                series: [{ name: 'Décès', data: data.zonesChart.series }],
                xaxis: { categories: data.zonesChart.labels },
                plotOptions: { bar: { horizontal: true } },
                colors: ['#10b981']
            });

            renderChart('ageSexChart', {
                chart: { type: 'bar', height: 350, stacked: true, toolbar: { show: false } },
                series: [
                    { name: 'Hommes', data: data.ageSexChart.hommes },
                    { name: 'Femmes', data: data.ageSexChart.femmes }
                ],
                xaxis: {
                    categories: data.ageSexChart.labels,
                    labels: { formatter: (val) => Math.abs(val) }
                },
                yaxis: { title: { text: 'Tranches d\'âge' } },
                plotOptions: { bar: { horizontal: true } },
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: (val) => Math.abs(val) } },
                colors: ['#3b82f6', '#ec4899']
            });
        })
        .catch(error => console.error('Erreur de chargement des graphiques:', error));
});