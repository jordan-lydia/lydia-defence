<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Gestionnaire') {
    header('Location: connexion.php');
    exit();
}

$gestionnaire_id = $_SESSION['user_id'];

try {
    // Récupérer toutes les déclarations traitées (validées OU rejetées) PAR CE GESTIONNAIRE
    $sql = "
        SELECT 
            dd.id, dd.date_deces, dd.date_validation, dd.statut_validation,
            zs.nom_zone, 
            u.prenom as enqueteur_prenom, u.nom as enqueteur_nom
        FROM declarations_deces dd
        JOIN zones_sante zs ON dd.zone_sante_id = zs.id
        JOIN utilisateurs u ON dd.enqueteur_id = u.id
        WHERE dd.validateur_id = ?
        ORDER BY dd.date_validation DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$gestionnaire_id]);
    $declarations_history = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération de l'historique: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Validations - Gestionnaire</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/gestionnaire_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/gestionnaire_navbar.php'; ?>
        
        <div class="page-header mb-4">
            <h1 class="page-title">Historique de vos Actions</h1>
            <p class="page-lead">Retrouvez ici toutes les déclarations que vous avez validées ou rejetées.</p>
        </div>
        
        <div class="content-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Traité le</th>
                            <th>Date du Décès</th>
                            <th>Zone</th>
                            <th>Soumis par</th>
                            <th class="text-center">Votre Décision</th>
                            <th class="text-end">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($declarations_history)): ?>
                            <tr><td colspan="6" class="text-center p-5 text-muted">
                                Vous n'avez encore traité aucune déclaration.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($declarations_history as $dec): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($dec['date_validation']))) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($dec['date_deces']))) ?></td>
                                    <td><?= htmlspecialchars($dec['nom_zone']) ?></td>
                                    <td><?= htmlspecialchars($dec['enqueteur_prenom'] . ' ' . $dec['enqueteur_nom']) ?></td>
                                    <td class="text-center">
                                        <?php
                                            $status = $dec['statut_validation'];
                                            $badge_class = 'text-bg-secondary';
                                            if ($status == 'valide') $badge_class = 'text-bg-success';
                                            elseif ($status == 'rejete') $badge_class = 'text-bg-danger';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="<?= $dec['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- MODAL pour voir les détails d'une déclaration passée -->
<div class="modal fade" id="historyDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="historyDetailsModalContent">
            <!-- Contenu chargé par AJAX -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const historyModal = new bootstrap.Modal(document.getElementById('historyDetailsModal'));
    const historyTable = document.querySelector('.table');

    if (historyTable) {
        historyTable.addEventListener('click', function(e) {
            const viewButton = e.target.closest('.btn-view-details');
            if (viewButton) {
                const declarationId = viewButton.dataset.id;
                const modalContent = document.getElementById('historyDetailsModalContent');
                
                modalContent.innerHTML = '<div class="modal-body text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
                historyModal.show();

                // On réutilise le même script API que la page de liste de l'admin car il fait déjà tout le travail
                fetch(`api/get_declaration_details.php?id=${declarationId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Erreur de chargement');
                        return response.text();
                    })
                    .then(html => {
                        // On modifie juste le HTML reçu pour enlever le bouton d'action
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        doc.querySelector('.modal-footer .btn-success')?.remove(); // Supprime le bouton "Prendre une décision"
                        modalContent.innerHTML = doc.body.innerHTML;
                    })
                    .catch(error => {
                        modalContent.innerHTML = `<div class="modal-body"><div class="alert alert-danger">${error.message}</div></div>`;
                    });
            }
        });
    }
});
</script>
</body>
</html>