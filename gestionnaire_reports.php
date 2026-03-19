<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Gestionnaire', 'Administrateur'])) {
    header('Location: connexion.php');
    exit();
}

try {
    $zones = $pdo->query("SELECT id, nom_zone FROM zones_sante ORDER BY nom_zone ASC")->fetchAll();
    $causes = $pdo->query("SELECT id, nom_cause FROM causes_deces ORDER BY nom_cause ASC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération des filtres: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Rapports - Gestionnaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/gestionnaire_reports.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/gestionnaire_sidebar.php'; ?>
    <div class="main-panel">
        <?php include 'includes/gestionnaire_navbar.php'; ?>
        <main class="content-wrapper">
            
            <div class="page-header mb-4">
                <h1 class="page-title">Générateur de Rapports</h1>
                <p class="page-lead">Créez des rapports PDF personnalisés basés sur les données validées.</p>
            </div>

            <div class="row g-4">
                <!-- Modèles de rapports pré-configurés -->
                <div class="col-md-6 col-lg-4">
                    <div class="report-card" data-report-type="monthly">
                        <div class="report-icon"><i class="fas fa-calendar-alt"></i></div>
                        <h5 class="report-title">Rapport Mensuel</h5>
                        <p class="report-description">Génère un rapport consolidé pour le mois écoulé.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="report-card" data-report-type="by_zone">
                        <div class="report-icon"><i class="fas fa-map-marked-alt"></i></div>
                        <h5 class="report-title">Rapport par Zone</h5>
                        <p class="report-description">Analyse détaillée d'une zone de santé sur une période donnée.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="report-card" data-report-type="full">
                        <div class="report-icon"><i class="fas fa-globe-africa"></i></div>
                        <h5 class="report-title">Rapport Général</h5>
                        <p class="report-description">Rapport complet de toutes les données validées (peut être long).</p>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- MODAL pour configurer et générer le rapport -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Configurer le Rapport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <input type="hidden" name="report_type" id="report_type">
                    <div class="mb-3">
                        <label for="date_start" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date_start" name="date_start">
                    </div>
                    <div class="mb-3">
                        <label for="date_end" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_end" name="date_end">
                    </div>
                    <!-- Ce champ ne sera visible que pour certains types de rapports -->
                    <div class="mb-3 d-none" id="zone_field">
                        <label for="zone_id" class="form-label">Zone de Santé</label>
                        <select class="form-select" id="zone_id" name="zone_id">
                            <option value="">Choisir une zone...</option>
                            <?php foreach($zones as $z) echo "<option value='{$z['id']}'>".htmlspecialchars($z['nom_zone'])."</option>"; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-danger" form="reportForm"><i class="fas fa-file-pdf me-2"></i>Générer le PDF</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/gestionnaire_reports.js"></script>
</body>
</html>