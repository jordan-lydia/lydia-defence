document.addEventListener('DOMContentLoaded', function() {
    const alertForm = document.getElementById('alertForm');

    alertForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const message = formData.get('message').trim();
        const targetText = document.querySelector('#target option:checked').textContent;

        if (!message) {
            Swal.fire('Erreur', 'Le message ne peut pas être vide.', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirmer l\'envoi',
            html: `Vous êtes sur le point d'envoyer une alerte à :<br><strong>${targetText}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, envoyer !',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                // Affiche un indicateur de chargement
                Swal.fire({
                    title: 'Envoi en cours...',
                    text: 'Veuillez patienter.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('api/send_alert_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Succès !', data.message, 'success');
                        alertForm.reset(); // Réinitialise le formulaire
                    } else {
                        Swal.fire('Erreur', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Erreur technique', 'Impossible de contacter le serveur.', 'error');
                    console.error('Fetch error:', error);
                });
            }
        });
    });
});