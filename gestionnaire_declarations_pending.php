<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Gestionnaire') {
    header('Location: connexion.php');
    exit();
}

try {
    $sql = "
        SELECT 
            dd.id, dd.date_deces, dd.date_saisie,
            zs.nom_zone, 
            u.prenom as enqueteur_prenom, u.nom as enqueteur_nom
        FROM declarations_deces dd
        JOIN zones_sante zs ON dd.zone_sante_id = zs.id
        JOIN utilisateurs u ON dd.enqueteur_id = u.id
        WHERE dd.statut_validation = 'en_attente'
        ORDER BY dd.date_saisie ASC";
    $stmt = $pdo->query($sql);
    $declarations_pending = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération des déclarations en attente: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déclarations à Valider - Gestionnaire</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/gestionnaire_declarations_pending.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/gestionnaire_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/gestionnaire_navbar.php'; ?>
        
        <div class="page-header mb-4">
            <h1 class="page-title">Déclarations à Valider</h1>
            <p class="page-lead">Traitez les déclarations soumises par les enquêteurs qui sont en attente de votre approbation.</p>
        </div>
        
        <div class="content-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="pendingTable">
                    <thead>
                        <tr>
                            <th>Soumis le</th>
                            <th>Date du Décès</th>
                            <th>Zone de Santé</th>
                            <th>Soumis par</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($declarations_pending)): ?>
                            <tr id="no-pending-row">
                                <td colspan="5" class="text-center p-5 text-muted">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i><br>
                                    Félicitations, vous êtes à jour ! Aucune déclaration à traiter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($declarations_pending as $dec): ?>
                                <tr id="declaration-row-<?= $dec['id'] ?>">
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($dec['date_saisie']))) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($dec['date_deces']))) ?></td>
                                    <td><?= htmlspecialchars($dec['nom_zone']) ?></td>
                                    <td><?= htmlspecialchars($dec['enqueteur_prenom'] . ' ' . $dec['enqueteur_nom']) ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-primary btn-treat" data-id="<?= $dec['id'] ?>">
                                            <i class="fas fa-search-plus me-2"></i>Traiter
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

<!-- MODAL 1: Affichage des détails (Conteneur vide prêt à être rempli) -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="detailsModalContent">
            <!-- Le contenu (header, body, footer) sera injecté ici par AJAX -->
        </div>
    </div>
</div>

<!-- MODAL 2: Formulaire d'action (Valider/Rejeter) -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Action sur la Déclaration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Après examen, veuillez choisir une action ci-dessous.</p>
                <form id="actionForm">
                    <input type="hidden" name="declaration_id" id="declaration_id">
                    <div class="mb-3">
                        <label for="commentaire_validation" class="form-label">Commentaire (obligatoire si rejeté)</label>
                        <textarea class="form-control" id="commentaire_validation" name="commentaire" rows="3" placeholder="Expliquez la raison du rejet..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" id="btnReject"><i class="fas fa-times me-2"></i>Rejeter</button>
                <button type="button" class="btn btn-success" id="btnApprove"><i class="fas fa-check me-2"></i>Valider</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/gestionnaire_declarations_pending.js"></script>
</body>
</html>