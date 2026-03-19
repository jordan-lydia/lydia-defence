document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const resultsContainer = document.getElementById('resultsContainer');
    const initialState = document.getElementById('initialState');
    const kpiContainer = document.getElementById('kpiContainer');
    const chartContainer = document.getElementById('mainChart');
    const tableContainer = document.getElementById('dataTableContainer');
    let mainChart;

    function renderChart(data) {
        const options = {
            series: [{ name: 'Décès', data: data.series }],
            chart: { type: 'bar', height: 350, toolbar: { show: false }},
            xaxis: { categories: data.labels },
            plotOptions: { bar: { horizontal: false, columnWidth: '50%' }},
            dataLabels: { enabled: false },
            colors: ['#3b82f6']
        };
        if (mainChart) mainChart.destroy();
        mainChart = new ApexCharts(chartContainer, options);
        mainChart.render();
    }

    function updateKpis(kpis) {
        kpiContainer.innerHTML = `
            <div class="col-md-6"><div class="stat-card"><div class="card-icon bg-danger-light"><i class="fas fa-cross"></i></div><div class="card-info"><h6>Total Décès (sélection)</h6><h5>${kpis.total_deces}</h5></div></div></div>
            <div class="col-md-6"><div class="stat-card"><div class="card-icon bg-primary-light"><i class="fas fa-birthday-cake"></i></div><div class="card-info"><h6>Âge Moyen (sélection)</h6><h5>${kpis.age_moyen} ans</h5></div></div></div>
        `;
    }

    function updateTable(data) {
        let tableHtml = '<table class="table table-sm table-hover"><thead><tr><th>Date</th><th>Âge</th><th>Sexe</th><th>Zone</th><th>Cause</th></tr></thead><tbody>';
        if(data.length === 0) {
            tableHtml += '<tr><td colspan="5" class="text-center text-muted p-4">Aucun résultat pour cette sélection.</td></tr>';
        } else {
            data.forEach(row => {
                tableHtml += `<tr><td>${new Date(row.date_deces).toLocaleDateString('fr-FR')}</td><td>${row.age_annees}</td><td>${row.sexe}</td><td>${row.nom_zone}</td><td>${row.nom_cause}</td></tr>`;
            });
        }
        tableHtml += '</tbody></table>';
        tableContainer.innerHTML = tableHtml;
    }

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        resultsContainer.classList.add('loading');
        initialState.classList.add('d-none');
        resultsContainer.classList.remove('d-none');

        const params = new URLSearchParams(new FormData(this)).toString();
        
        fetch(`api/explorer_data_action.php?${params}`)
            .then(response => response.json())
            .then(data => {
                resultsContainer.classList.remove('loading');
                if (data.success) {
                    updateKpis(data.kpis);
                    renderChart(data.chartData);
                    updateTable(data.tableData);
                } else {
                    Swal.fire('Erreur', data.message || 'Impossible de récupérer les données.', 'error');
                }
            })
            .catch(() => {
                resultsContainer.classList.remove('loading');
                Swal.fire('Erreur', 'Un problème de communication est survenu.', 'error');
            });
    });

    document.getElementById('resetFilters').addEventListener('click', function() {
        resultsContainer.classList.add('d-none');
        initialState.classList.remove('d-none');
    });
});