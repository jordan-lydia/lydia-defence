document.addEventListener('DOMContentLoaded', function() {
    const detailsModalEl = document.getElementById('detailsModal');
    const detailsModal = new bootstrap.Modal(detailsModalEl);
    const actionModal = new bootstrap.Modal(document.getElementById('actionModal'));
    const pendingTable = document.getElementById('pendingTable');

    let currentDeclarationId = null;

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

    pendingTable.addEventListener('click', function(e) {
        const treatButton = e.target.closest('.btn-treat');
        if (treatButton) {
            currentDeclarationId = treatButton.dataset.id;
            const modalContent = document.getElementById('detailsModalContent');
            
            modalContent.innerHTML = '<div class="modal-body"><div class="spinner-container"><div class="spinner-border text-primary" role="status"></div></div></div>';
            detailsModal.show();

            fetch(`api/get_declaration_details.php?id=${currentDeclarationId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error(text) });
                    }
                    return response.text();
                })
                .then(html => {
                    modalContent.innerHTML = html;
                })
                .catch(error => {
                    modalContent.innerHTML = `<div class="modal-header"><h5 class="modal-title">Erreur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="alert alert-danger m-0">${error.message}</div></div>`;
                });
        }
    });

    detailsModalEl.addEventListener('click', function(e) {
        if (e.target.id === 'openActionModalBtn') {
            detailsModal.hide();
            document.getElementById('declaration_id').value = currentDeclarationId;
            document.getElementById('actionForm').reset();
            actionModal.show();
        }
    });

    function handleAction(action) {
        const form = document.getElementById('actionForm');
        const formData = new FormData(form);
        formData.append('action', action);

        fetch('api/validation_actions.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                actionModal.hide();
                if (data.success) {
                    showToast('success', data.message);
                    const rowToRemove = document.getElementById(`declaration-row-${currentDeclarationId}`);
                    if (rowToRemove) {
                        rowToRemove.style.transition = 'opacity 0.5s';
                        rowToRemove.style.opacity = '0';
                        setTimeout(() => rowToRemove.remove(), 500);
                    }
                } else {
                    showToast('error', data.message);
                }
            })
            .catch(() => showToast('error', 'Erreur de communication.'));
    }

    document.getElementById('btnApprove').addEventListener('click', () => handleAction('approve'));
    document.getElementById('btnReject').addEventListener('click', () => {
        if (!document.getElementById('commentaire_validation').value.trim()) {
            showToast('warning', 'Un commentaire est requis pour rejeter.');
            return;
        }
        handleAction('reject');
    });
});