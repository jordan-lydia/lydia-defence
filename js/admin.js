document.addEventListener('DOMContentLoaded', function() {
    
    // --- Graphique 1 : Tendances des Décès (données venant de PHP) ---
    const deathsOverTimeOptions = {
        series: [{
            name: 'Décès déclarés',
            data: seriesTendances // Variable PHP
        }],
        chart: {
            height: 350,
            type: 'area',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            type: 'category',
            categories: labelsTendances // Variable PHP
        },
        yaxis: {
            title: {
                text: 'Nombre de décès'
            }
        },
        tooltip: {
            x: {
                format: 'MMM'
            },
        },
        colors: ['#3b82f6']
    };

    if (document.querySelector("#deathsOverTimeChart")) {
        const deathsOverTimeChart = new ApexCharts(document.querySelector("#deathsOverTimeChart"), deathsOverTimeOptions);
        deathsOverTimeChart.render();
    }


    // --- Graphique 2 : Répartition des décès par cause (données venant de PHP) ---
    const deathsByCauseOptions = {
        series: seriesCauses, // Variable PHP
        chart: {
            height: 350,
            type: 'donut',
        },
        labels: labelsCauses, // Variable PHP
        legend: {
            position: 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: '100%'
                }
            }
        }],
        colors: ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#6b7280']
    };

    if (document.querySelector("#deathsByCauseChart")) {
        const deathsByCauseChart = new ApexCharts(document.querySelector("#deathsByCauseChart"), deathsByCauseOptions);
        deathsByCauseChart.render();
    }
});