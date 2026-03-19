<?php
session_start();
require_once 'includes/db.php';

// Sécurité : vérifier si l'utilisateur est connecté et est un Gestionnaire
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Gestionnaire') {
    header('Location: connexion.php');
    exit();
}

$gestionnaire_id = $_SESSION['user_id'];

try {
    // KPI 1: Nombre de déclarations en attente de validation
    $stmt_pending = $pdo->query("SELECT COUNT(id) FROM declarations_deces WHERE statut_validation = 'en_attente'");
    $declarations_a_valider = $stmt_pending->fetchColumn();

    // KPI 2: Déclarations validées par ce gestionnaire ce mois-ci
    $stmt_validated = $pdo->prepare("SELECT COUNT(id) FROM declarations_deces WHERE validateur_id = ? AND statut_validation = 'valide' AND MONTH(date_validation) = MONTH(CURDATE()) AND YEAR(date_validation) = YEAR(CURDATE())");
    $stmt_validated->execute([$gestionnaire_id]);
    $declarations_validees_mois = $stmt_validated->fetchColumn();
    
    // KPI 3: Taux de rejet global
    $stmt_total = $pdo->query("SELECT COUNT(id) FROM declarations_deces WHERE statut_validation IN ('valide', 'rejete')");
    $total_processed = $stmt_total->fetchColumn();
    $stmt_rejected = $pdo->query("SELECT COUNT(id) FROM declarations_deces WHERE statut_validation = 'rejete'");
    $total_rejected = $stmt_rejected->fetchColumn();
    $taux_rejet = ($total_processed > 0) ? round(($total_rejected / $total_processed) * 100, 1) : 0;

    // Données pour le tableau des dernières déclarations à traiter
    $sql_recent_pending = "
        SELECT dd.id, dd.date_deces, zs.nom_zone, CONCAT(u.prenom, ' ', u.nom) as nom_enqueteur
        FROM declarations_deces dd
        JOIN zones_sante zs ON dd.zone_sante_id = zs.id
        JOIN utilisateurs u ON dd.enqueteur_id = u.id
        WHERE dd.statut_validation = 'en_attente'
        ORDER BY dd.date_saisie ASC
        LIMIT 5";
    $stmt_recent_pending = $pdo->query($sql_recent_pending);
    $declarations_recentes = $stmt_recent_pending->fetchAll();

    // Données pour le graphique des validations par jour (7 derniers jours)
    $sql_validation_trend = "
        SELECT DATE(date_validation) as jour, COUNT(id) as total
        FROM declarations_deces
        WHERE validateur_id = ? AND date_validation >= NOW() - INTERVAL 7 DAY
        GROUP BY jour
        ORDER BY jour ASC";
    $stmt_validation_trend = $pdo->prepare($sql_validation_trend);
    $stmt_validation_trend->execute([$gestionnaire_id]);
    $validation_trend_data = $stmt_validation_trend->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $labels_trend = [];
    $series_trend = [];
    for ($i=6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels_trend[] = date('d/m', strtotime($date));
        $series_trend[] = $validation_trend_data[$date] ?? 0;
    }

} catch (PDOException $e) {
    die("Erreur de récupération des données: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestionnaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css"> <!-- Réutilisation du CSS admin -->
</head>
<body class="admin-body">

<div class="admin-wrapper">
    
    <?php include 'includes/gestionnaire_sidebar.php'; ?>

    <main class="content-wrapper">
        
        <?php include 'includes/gestionnaire_navbar.php'; ?>
        
        <div class="page-header mb-4">
            <h1 class="page-title">Tableau de Bord du Gestionnaire</h1>
            <p class="page-lead">Vue d'ensemble de vos tâches de validation et d'analyse.</p>
        </div>

        <!-- KPIs spécifiques au Gestionnaire -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="card-icon bg-warning-light"><i class="fas fa-hourglass-half"></i></div>
                    <div class="card-info"><h6>A Valider</h6><h5><?= $declarations_a_valider ?></h5></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="card-icon bg-success-light"><i class="fas fa-check-double"></i></div>
                    <div class="card-info"><h6>Validées (ce mois)</h6><h5><?= $declarations_validees_mois ?></h5></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="card-icon bg-danger-light"><i class="fas fa-times-circle"></i></div>
                    <div class="card-info"><h6>Taux de Rejet Global</h6><h5><?= $taux_rejet ?>%</h5></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Tableau des déclarations à traiter -->
            <div class="col-lg-8">
                <div class="content-block">
                    <h5 class="content-block-header">Déclarations les plus anciennes en attente</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Date Décès</th><th>Zone</th><th>Soumis par</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php if (empty($declarations_recentes)): ?>
                                    <tr><td colspan="4" class="text-center p-4 text-muted">Bravo, aucune déclaration en attente !</td></tr>
                                <?php else: ?>
                                    <?php foreach ($declarations_recentes as $dec): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($dec['date_deces']))) ?></td>
                                        <td><?= htmlspecialchars($dec['nom_zone']) ?></td>
                                        <td><?= htmlspecialchars($dec['nom_enqueteur']) ?></td>
                                        <td><a href="gestionnaire_declarations_pending.php?id=<?= $dec['id'] ?>" class="btn btn-sm btn-primary">Traiter</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Graphique de productivité -->
            <div class="col-lg-4">
                <div class="content-block">
                    <h5 class="content-block-header">Votre Activité (7 derniers jours)</h5>
                    <div id="validationTrendChart"></div>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // Passer les données PHP au JavaScript pour le graphique
    const labelsTrend = <?= json_encode($labels_trend) ?>;
    const seriesTrend = <?= json_encode($series_trend) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const validationTrendOptions = {
            series: [{ name: 'Déclarations traitées', data: seriesTrend }],
            chart: { type: 'bar', height: 310, toolbar: { show: false }},
            plotOptions: { bar: { horizontal: false, columnWidth: '40%', borderRadius: 4 }},
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            xaxis: { categories: labelsTrend },
            yaxis: { title: { text: 'Nombre de validations' }},
            fill: { opacity: 1 },
            colors: ['#3b82f6'],
            tooltip: { y: { formatter: function (val) { return val + " validations" } } }
        };

        const validationTrendChart = new ApexCharts(document.querySelector("#validationTrendChart"), validationTrendOptions);
        validationTrendChart.render();
    });
</script>
</body>
</html>