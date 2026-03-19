<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

try {
    $sql = "
        SELECT 
            dd.id, dd.date_deces, dd.sexe, dd.age_annees, dd.statut_validation,
            zs.nom_zone, 
            cd.nom_cause,
            enqueteur.prenom as enqueteur_prenom, enqueteur.nom as enqueteur_nom,
            validateur.prenom as validateur_prenom, validateur.nom as validateur_nom,
            dd.date_validation, dd.commentaire_validation, dd.cause_probable_texte
        FROM declarations_deces dd
        JOIN zones_sante zs ON dd.zone_sante_id = zs.id
        JOIN causes_deces cd ON dd.cause_deces_id = cd.id
        JOIN utilisateurs enqueteur ON dd.enqueteur_id = enqueteur.id
        LEFT JOIN utilisateurs validateur ON dd.validateur_id = validateur.id
        ORDER BY dd.date_saisie DESC";
    $stmt = $pdo->query($sql);
    $declarations = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération des déclarations: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Déclarations - Admin STDM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_declarations_list.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/admin_navbar.php'; ?>
        
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Toutes les Déclarations</h1>
                <p class="page-lead">Consultez l'historique complet de toutes les déclarations de décès.</p>
            </div>
        </div>
        
        <div class="content-block">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par zone, cause, enquêteur...">
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente">En attente</option>
                        <option value="valide">Validé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="declarationsTable">
                    <thead>
                        <tr>
                            <th>Date Décès</th>
                            <th>Zone</th>
                            <th>Cause</th>
                            <th>Enquêteur</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($declarations as $dec): ?>
                        <tr class="declaration-row" data-id="<?= $dec['id'] ?>">
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($dec['date_deces']))) ?></td>
                            <td class="data-zone"><?= htmlspecialchars($dec['nom_zone']) ?></td>
                            <td class="data-cause"><?= htmlspecialchars($dec['nom_cause']) ?></td>
                            <td class="data-enqueteur"><?= htmlspecialchars($dec['enqueteur_prenom'] . ' ' . $dec['enqueteur_nom']) ?></td>
                            <td class="text-center data-status" data-status-value="<?= $dec['statut_validation'] ?>">
                                <?php
                                    $status = $dec['statut_validation'];
                                    $badge_class = 'text-bg-secondary';
                                    if ($status == 'en_attente') $badge_class = 'text-bg-warning';
                                    elseif ($status == 'valide') $badge_class = 'text-bg-success';
                                    elseif ($status == 'rejete') $badge_class = 'text-bg-danger';
                                    echo "<span class=\"badge $badge_class\">" . ucfirst($status) . "</span>";
                                ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary btn-view-details" title="Voir les détails"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- MODAL POUR VOIR LES DÉTAILS D'UNE DÉCLARATION -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Détails de la Déclaration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <!-- Le contenu sera chargé ici par AJAX -->
                <div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin_declarations_list.js"></script>
</body>
</html>