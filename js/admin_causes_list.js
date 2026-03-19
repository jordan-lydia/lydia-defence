document.addEventListener('DOMContentLoaded', function() {
    const causeModal = new bootstrap.Modal(document.getElementById('causeModal'));
    const causeForm = document.getElementById('causeForm');
    const causeModalLabel = document.getElementById('causeModalLabel');

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

    document.querySelector('[data-bs-target="#causeModal"]').addEventListener('click', function() {
        causeModalLabel.textContent = 'Ajouter une Cause de Décès';
        causeForm.reset();
        document.getElementById('cause_id').value = '';
    });

    document.getElementById('causesTable').addEventListener('click', function(e) {
        const editButton = e.target.closest('.btn-edit-cause');
        if (editButton) {
            causeModalLabel.textContent = 'Modifier la Cause de Décès';
            causeForm.reset();
            document.getElementById('cause_id').value = editButton.dataset.causeId;
            document.getElementById('nom_cause').value = editButton.dataset.causeNom;
            document.getElementById('categorie').value = editButton.dataset.causeCategorie;
            document.getElementById('code_cim10').value = editButton.dataset.causeCode;
            causeModal.show();
        }

        const deleteButton = e.target.closest('.btn-delete-cause');
        if (deleteButton) {
            const causeId = deleteButton.dataset.causeId;
            const causeName = deleteButton.dataset.causeName;

            Swal.fire({
                title: 'Confirmer la suppression',
                html: `Voulez-vous vraiment supprimer la cause <strong>${causeName}</strong> ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete_cause');
                    formData.append('cause_id', causeId);

                    fetch('api/cause_actions.php', { method: 'POST', body: formData })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('success', data.message);
                                document.getElementById(`cause-row-${causeId}`).remove();
                            } else {
                                showToast('error', data.message);
                            }
                        });
                }
            });
        }
    });

    causeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'save_cause');

        fetch('api/cause_actions.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    causeModal.hide();
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('error', data.message);
                }
            });
    });
});