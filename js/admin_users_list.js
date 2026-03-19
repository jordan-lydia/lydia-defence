document.addEventListener('DOMContentLoaded', function() {
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    const userForm = document.getElementById('userForm');
    const userModalLabel = document.getElementById('userModalLabel');
    const tableBody = document.querySelector('#users-table tbody');

    function showToast(icon, title) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    document.querySelector('[data-bs-target="#userModal"]').addEventListener('click', function() {
        userModalLabel.textContent = 'Ajouter un utilisateur';
        userForm.reset();
        document.getElementById('user_id').value = '';
        document.getElementById('passwordHelp').textContent = 'Le mot de passe est obligatoire.';
        document.getElementById('mot_de_passe').required = true;
    });

    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit')) {
            const button = e.target.closest('.btn-edit');
            userModalLabel.textContent = 'Modifier l\'utilisateur';
            userForm.reset();
            
            document.getElementById('user_id').value = button.dataset.userId;
            document.getElementById('nom').value = button.dataset.userNom;
            document.getElementById('prenom').value = button.dataset.userPrenom;
            document.getElementById('email').value = button.dataset.userEmail;
            document.getElementById('role_id').value = button.dataset.userRoleId;
            document.getElementById('statut_compte').value = button.dataset.userStatut;
            document.getElementById('passwordHelp').textContent = 'Laissez vide pour ne pas changer le mot de passe.';
            document.getElementById('mot_de_passe').required = false;

            userModal.show();
        }
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const userId = button.dataset.userId;
            const userName = button.dataset.userName;

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                html: `Voulez-vous vraiment supprimer <strong>${userName}</strong> ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete_user');
                    formData.append('user_id', userId);

                    fetch('api/user_actions.php', { method: 'POST', body: formData })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('success', data.message);
                                document.getElementById(`user-row-${userId}`).remove();
                            } else {
                                showToast('error', data.message);
                            }
                        });
                }
            });
        }
    });

    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'save_user');

        fetch('api/user_actions.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userModal.hide();
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 1500); // Recharger pour voir les changements
                } else {
                    showToast('error', data.message);
                }
            });
    });
});