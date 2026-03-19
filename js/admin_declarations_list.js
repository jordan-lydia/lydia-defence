document.addEventListener('DOMContentLoaded', function() {
    const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const detailsModalBody = document.getElementById('detailsModalBody');
    const tableBody = document.getElementById('declarationsTable').querySelector('tbody');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');

    tableBody.addEventListener('click', function(e) {
        const row = e.target.closest('.declaration-row');
        if (row) {
            const declarationId = row.dataset.id;
            
            // Affiche le spinner de chargement
            detailsModalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            detailsModal.show();

            // Récupère les détails via AJAX
            fetch(`api/get_declaration_details.php?id=${declarationId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    detailsModalBody.innerHTML = html;
                })
                .catch(error => {
                    detailsModalBody.innerHTML = `<div class="alert alert-danger">Erreur de chargement des détails.</div>`;
                    console.error('Fetch error:', error);
                });
        }
    });

    // Fonction de filtrage
    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        Array.from(tableBody.querySelectorAll('tr')).forEach(row => {
            const zone = row.querySelector('.data-zone').textContent.toLowerCase();
            const cause = row.querySelector('.data-cause').textContent.toLowerCase();
            const enqueteur = row.querySelector('.data-enqueteur').textContent.toLowerCase();
            const status = row.querySelector('.data-status').dataset.statusValue;

            const textMatch = (zone.includes(searchText) || cause.includes(searchText) || enqueteur.includes(searchText));
            const statusMatch = (statusValue === '' || status === statusValue);

            if (textMatch && statusMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('keyup', filterTable);
    statusFilter.addEventListener('change', filterTable);
});