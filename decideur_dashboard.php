<?php
session_start();
require_once 'includes/db.php';

// Sécurité : vérifier si l'utilisateur est connecté et est un Décideur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Décideur') {
    header('Location: connexion.php');
    exit();
}

try {
    // KPI 1: Total des décès ce mois-ci
    $stmt_total_mois = $pdo->query("SELECT COUNT(id) FROM declarations_deces WHERE MONTH(date_deces) = MONTH(CURDATE()) AND YEAR(date_deces) = YEAR(CURDATE())");
    $total_deces_mois = $stmt_total_mois->fetchColumn();

    // KPI 2: Comparaison avec le mois précédent
    $stmt_total_mois_prec = $pdo->query("SELECT COUNT(id) FROM declarations_deces WHERE MONTH(date_deces) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(date_deces) = YEAR(CURDATE() - INTERVAL 1 MONTH)");
    $total_deces_mois_prec = $stmt_total_mois_prec->fetchColumn();
    $variation_mensuelle = ($total_deces_mois_prec > 0) ? round((($total_deces_mois - $total_deces_mois_prec) / $total_deces_mois_prec) * 100, 1) : 100;

    // KPI 3: Cause de décès principale ce mois-ci
    $stmt_cause_principale = $pdo->query("SELECT cd.nom_cause FROM declarations_deces dd JOIN causes_deces cd ON dd.cause_deces_id = cd.id WHERE MONTH(dd.date_deces) = MONTH(CURDATE()) AND YEAR(dd.date_deces) = YEAR(CURDATE()) GROUP BY dd.cause_deces_id ORDER BY COUNT(dd.id) DESC LIMIT 1");
    $cause_principale = $stmt_cause_principale->fetchColumn() ?: 'N/A';
    
    // KPI 4: Zone la plus touchée ce mois-ci
    $stmt_zone_principale = $pdo->query("SELECT zs.nom_zone FROM declarations_deces dd JOIN zones_sante zs ON dd.zone_sante_id = zs.id WHERE MONTH(dd.date_deces) = MONTH(CURDATE()) AND YEAR(dd.date_deces) = YEAR(CURDATE()) GROUP BY dd.zone_sante_id ORDER BY COUNT(dd.id) DESC LIMIT 1");
    $zone_principale = $stmt_zone_principale->fetchColumn() ?: 'N/A';

    // Données pour le graphique de répartition par cause (identique à l'admin)
    $sql_causes = "SELECT cd.nom_cause, COUNT(dd.id) as total FROM declarations_deces dd JOIN causes_deces cd ON dd.cause_deces_id = cd.id GROUP BY cd.nom_cause ORDER BY total DESC LIMIT 5";
    $stmt_causes = $pdo->query($sql_causes);
    $data_causes = $stmt_causes->fetchAll();
    $labels_causes = array_column($data_causes, 'nom_cause');
    $series_causes = array_map('intval', array_column($data_causes, 'total'));

} catch (PDOException $e) {
    die("Erreur de récupération des données stratégiques: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Stratégique - Décideur</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css"> <!-- Réutilisation du CSS admin -->
</head>
<body class="admin-body">

<div class="admin-wrapper">
    
    <?php include 'includes/decideur_sidebar.php'; ?>

    <main class="content-wrapper">
        
        <?php include 'includes/decideur_navbar.php'; ?>
        
        <div class="page-header mb-4">
            <h1 class="page-title">Dashboard Stratégique Global</h1>
            <p class="page-lead">Vue synthétique des indicateurs de mortalité pour la prise de décision.</p>
        </div>

        <!-- KPIs de haut niveau pour le Décideur -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="card-icon bg-primary-light"><i class="fas fa-calendar-alt"></i></div>
                    <div class="card-info"><h6>Décès (ce mois)</h6><h5><?= $total_deces_mois ?></h5></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="card-icon <?= $variation_mensuelle >= 0 ? 'bg-danger-light' : 'bg-success-light' ?>">
                        <i class="fas <?= $variation_mensuelle >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i>
                    </div>
                    <div class="card-info"><h6>Variation / Mois Préc.</h6><h5 class="<?= $variation_mensuelle >= 0 ? 'text-danger' : 'text-success' ?>"><?= $variation_mensuelle ?>%</h5></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="card-icon bg-warning-light"><i class="fas fa-lungs-virus"></i></div>
                    <div class="card-info"><h6>Cause n°1 (ce mois)</h6><h5 style="font-size: 1.2rem;"><?= htmlspecialchars($cause_principale) ?></h5></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="card-icon bg-info-light" style="background-color: #cffafe; color: #06b6d4;"><i class="fas fa-map-pin"></i></div>
                    <div class="card-info"><h6>Zone n°1 (ce mois)</h6><h5 style="font-size: 1.2rem;"><?= htmlspecialchars($zone_principale) ?></h5></div>
                </div>
            </div>
        </div>

        <!-- Vue des graphiques principaux -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="content-block">
                    <h5 class="content-block-header">Répartition Globale par Cause (Top 5)</h5>
                    <div id="deathsByCauseChart"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="content-block">
                    <h5 class="content-block-header">Carte des Zones à Risque</h5>
                    <!-- Ici, on intégrerait une vraie carte (ex: Leaflet.js) -->
                    <div class="text-center p-5">
                        <i class="fas fa-map-marked-alt fa-4x text-muted"></i>
                        <p class="mt-3 text-muted">La vue cartographique est en cours de développement.</p>
                        <a href="decideur_map_view.php" class="btn btn-sm btn-outline-primary">Voir la carte</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // Passer les données PHP au JavaScript pour le graphique
    const labelsCauses = <?= json_encode($labels_causes) ?>;
    const seriesCauses = <?= json_encode($series_causes) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const deathsByCauseOptions = {
            series: seriesCauses,
            chart: { type: 'polarArea', height: 350 },
            labels: labelsCauses,
            stroke: { colors: ['#fff'] },
            fill: { opacity: 0.8 },
            responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }]
        };

        const deathsByCauseChart = new ApexCharts(document.querySelector("#deathsByCauseChart"), deathsByCauseOptions);
        deathsByCauseChart.render();
    });
</script>
</body>
</html>