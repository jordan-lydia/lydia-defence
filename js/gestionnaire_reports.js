document.addEventListener('DOMContentLoaded', function() {
    const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
    const reportForm = document.getElementById('reportForm');
    const reportModalLabel = document.getElementById('reportModalLabel');

    document.querySelectorAll('.report-card').forEach(card => {
        card.addEventListener('click', function() {
            const reportType = this.dataset.reportType;
            const reportTitle = this.querySelector('.report-title').textContent;
            
            // Personnaliser le modal en fonction du type de rapport
            document.getElementById('report_type').value = reportType;
            reportModalLabel.textContent = `Générer : ${reportTitle}`;
            
            const zoneField = document.getElementById('zone_field');
            const dateStart = document.getElementById('date_start');
            const dateEnd = document.getElementById('date_end');
            const zoneSelect = document.getElementById('zone_id');

            // Cacher/afficher les champs pertinents
            dateStart.parentElement.classList.remove('d-none');
            dateEnd.parentElement.classList.remove('d-none');
            zoneField.classList.add('d-none');
            zoneSelect.required = false;

            // Pré-remplir les dates pour certains rapports
            if (reportType === 'monthly') {
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
                dateStart.value = firstDay.toISOString().split('T')[0];
                dateEnd.value = lastDay.toISOString().split('T')[0];
            } else if (reportType === 'by_zone') {
                zoneField.classList.remove('d-none');
                zoneSelect.required = true;
            } else if (reportType === 'full') {
                dateStart.parentElement.classList.add('d-none');
                dateEnd.parentElement.classList.add('d-none');
            }
            
            reportModal.show();
        });
    });

    reportForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // La génération de PDF se fait via une soumission de formulaire classique, pas en AJAX
        // car on veut que le navigateur déclenche un téléchargement.
        // On construit l'URL et on ouvre dans un nouvel onglet.
        const formData = new FormData(this);
        const params = new URLSearchParams(formData).toString();
        
        // On utilise un fichier PHP dédié pour la génération
        window.open(`admin_generate_custom_report.php?${params}`, '_blank');
        
        reportModal.hide();
        Swal.fire({
            title: 'Génération en cours...',
            text: 'Votre rapport va s\'ouvrir dans un nouvel onglet.',
            icon: 'info',
            timer: 2000,
            showConfirmButton: false
        });
    });
});