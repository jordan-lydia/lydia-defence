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
    <title>Explorateur de Données - Gestionnaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/gestionnaire_data_explorer.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/gestionnaire_sidebar.php'; ?>
    
    <!-- On déplace la navbar et le contenu dans une seule structure -->
    <div class="main-panel">
        <?php include 'includes/gestionnaire_navbar.php'; ?>
        
        <main class="content-wrapper">
            <div class="page-header mb-4">
                <h1 class="page-title">Explorateur de Données</h1>
                <p class="page-lead">Filtrez et analysez les données de mortalité validées pour découvrir des tendances.</p>
            </div>

            <!-- Section des Filtres -->
            <div class="content-block mb-4">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3"><label for="date_start" class="form-label">Date de début</label><input type="date" id="date_start" name="date_start" class="form-control"></div>
                    <div class="col-md-3"><label for="date_end" class="form-label">Date de fin</label><input type="date" id="date_end" name="date_end" class="form-control"></div>
                    <div class="col-md-3"><label for="zone_id" class="form-label">Zone de Santé</label><select id="zone_id" name="zone_id" class="form-select"><option value="">Toutes</option><?php foreach($zones as $z) echo "<option value='{$z['id']}'>".htmlspecialchars($z['nom_zone'])."</option>"; ?></select></div>
                    <div class="col-md-3"><label for="cause_id" class="form-label">Cause de Décès</label><select id="cause_id" name="cause_id" class="form-select"><option value="">Toutes</option><?php foreach($causes as $c) echo "<option value='{$c['id']}'>".htmlspecialchars($c['nom_cause'])."</option>"; ?></select></div>
                    <div class="col-md-3"><label for="sexe" class="form-label">Sexe</label><select id="sexe" name="sexe" class="form-select"><option value="">Tous</option><option value="M">Masculin</option><option value="F">Féminin</option></select></div>
                    <div class="col-md-3"><label for="age_range" class="form-label">Tranche d'âge</label><select id="age_range" name="age_range" class="form-select"><option value="">Toutes</option><option value="0-4">0-4 ans</option><option value="5-14">5-14 ans</option><option value="15-29">15-29 ans</option><option value="30-44">30-44 ans</option><option value="45-59">45-59 ans</option><option value="60-74">60-74 ans</option><option value="75+">75+ ans</option></select></div>
                    <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Appliquer les filtres</button></div>
                    <div class="col-md-3"><button type="reset" class="btn btn-outline-secondary w-100" id="resetFilters">Réinitialiser</button></div>
                </form>
            </div>

            <div id="resultsContainer" class="d-none">
                <div class="row g-4 mb-4" id="kpiContainer"></div>
                <div class="content-block mb-4">
                    <h5 class="content-block-header">Analyse Visuelle</h5>
                    <div id="mainChart"></div>
                </div>
                <div class="content-block">
                    <h5 class="content-block-header">Données Détaillées</h5>
                    <div class="table-responsive" id="dataTableContainer"></div>
                </div>
            </div>

            <div id="initialState" class="text-center p-5">
                <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                <h4>Prêt à explorer</h4>
                <p class="text-muted">Veuillez sélectionner vos filtres et cliquer sur "Appliquer" pour afficher les données.</p>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="js/gestionnaire_data_explorer.js"></script>
</body>
</html>