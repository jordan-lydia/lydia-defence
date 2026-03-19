document.addEventListener('DOMContentLoaded', function() {
    const zoneModal = new bootstrap.Modal(document.getElementById('zoneModal'));
    const zoneForm = document.getElementById('zoneForm');
    const zoneModalLabel = document.getElementById('zoneModalLabel');

    function showToast(icon, title) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });
    }

    document.querySelector('[data-bs-target="#zoneModal"]').addEventListener('click', function() {
        zoneModalLabel.textContent = 'Ajouter une Zone de Santé';
        zoneForm.reset();
        document.getElementById('zone_id').value = '';
    });

    document.getElementById('zonesTable').addEventListener('click', function(e) {
        const editButton = e.target.closest('.btn-edit-zone');
        if (editButton) {
            zoneModalLabel.textContent = 'Modifier la Zone de Santé';
            zoneForm.reset();
            document.getElementById('zone_id').value = editButton.dataset.zoneId;
            document.getElementById('nom_zone').value = editButton.dataset.zoneNom;
            document.getElementById('commune').value = editButton.dataset.zoneCommune;
            zoneModal.show();
        }

        const deleteButton = e.target.closest('.btn-delete-zone');
        if (deleteButton) {
            const zoneId = deleteButton.dataset.zoneId;
            const zoneName = deleteButton.dataset.zoneName;

            Swal.fire({
                title: 'Confirmer la suppression',
                html: `Voulez-vous vraiment supprimer la zone <strong>${zoneName}</strong> ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete_zone');
                    formData.append('zone_id', zoneId);

                    fetch('api/zone_actions.php', { method: 'POST', body: formData })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('success', data.message);
                                document.getElementById(`zone-row-${zoneId}`).remove();
                            } else {
                                showToast('error', data.message);
                            }
                        });
                }
            });
        }
    });

    zoneForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'save_zone');

        fetch('api/zone_actions.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    zoneModal.hide();
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('error', data.message);
                }
            });
    });
});