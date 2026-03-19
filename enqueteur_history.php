<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Enquêteur') {
    header('Location: connexion.php');
    exit();
}

$enqueteur_id = $_SESSION['user_id'];

try {
    // Historique pour la table
    $sql_history = "SELECT dd.id, dd.date_deces, dd.date_saisie, dd.statut_validation, zs.nom_zone, cd.nom_cause FROM declarations_deces dd JOIN zones_sante zs ON dd.zone_sante_id = zs.id JOIN causes_deces cd ON dd.cause_deces_id = cd.id WHERE dd.enqueteur_id = ? ORDER BY dd.date_saisie DESC";
    $stmt_history = $pdo->prepare($sql_history);
    $stmt_history->execute([$enqueteur_id]);
    $history = $stmt_history->fetchAll();

    // Données pour le graphique des causes
    $sql_causes = "SELECT cd.nom_cause, COUNT(dd.id) as total FROM declarations_deces dd JOIN causes_deces cd ON dd.cause_deces_id = cd.id WHERE dd.enqueteur_id = ? GROUP BY cd.nom_cause ORDER BY total DESC LIMIT 5";
    $stmt_causes = $pdo->prepare($sql_causes);
    $stmt_causes->execute([$enqueteur_id]);
    $causes_data = $stmt_causes->fetchAll(PDO::FETCH_KEY_PAIR);

    // Données pour le graphique des zones
    $sql_zones = "SELECT zs.nom_zone, COUNT(dd.id) as total FROM declarations_deces dd JOIN zones_sante zs ON dd.zone_sante_id = zs.id WHERE dd.enqueteur_id = ? GROUP BY zs.nom_zone ORDER BY total DESC LIMIT 5";
    $stmt_zones = $pdo->prepare($sql_zones);
    $stmt_zones->execute([$enqueteur_id]);
    $zones_data = $stmt_zones->fetchAll(PDO::FETCH_KEY_PAIR);

    // Données pour le graphique des âges
    $sql_ages = "
        SELECT 
            CASE 
                WHEN age_annees BETWEEN 0 AND 4 THEN '0-4 ans' WHEN age_annees BETWEEN 5 AND 14 THEN '5-14 ans'
                WHEN age_annees BETWEEN 15 AND 29 THEN '15-29 ans' WHEN age_annees BETWEEN 30 AND 44 THEN '30-44 ans'
                WHEN age_annees BETWEEN 45 AND 59 THEN '45-59 ans' WHEN age_annees BETWEEN 60 AND 74 THEN '60-74 ans'
                ELSE '75+ ans'
            END as age_group,
            COUNT(id) as total
        FROM declarations_deces WHERE enqueteur_id = ?
        GROUP BY age_group ORDER BY age_group ASC";
    $stmt_ages = $pdo->prepare($sql_ages);
    $stmt_ages->execute([$enqueteur_id]);
    $ages_data = $stmt_ages->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    die("Erreur de récupération des données: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Saisies - Enquêteur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/enqueteur_history.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/enqueteur_sidebar.php'; ?>
    <div class="main-panel">
        <?php include 'includes/enqueteur_navbar.php'; ?>
        <main class="content-wrapper">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Tableau de Bord de Mes Saisies</h1>
                    <p class="page-lead">Visualisez l'impact et l'historique de votre travail de collecte.</p>
                </div>
                <a href="enqueteur_generate_report.php" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf me-2"></i>Mon Rapport de Saisies</a>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="content-block">
                        <h5 class="content-block-header">Top 5 des Causes Reportées</h5>
                        <div id="causesChart"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-block">
                        <h5 class="content-block-header">Top 5 des Zones Opérées</h5>
                        <div id="zonesChart"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-block">
                        <h5 class="content-block-header">Répartition par Tranche d'Âge</h5>
                        <div id="agesChart"></div>
                    </div>
                </div>
            </div>
            
            <div class="content-block">
                <h5 class="content-block-header">Historique complet de vos saisies</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="historyTable">
                        <thead><tr><th>Soumis le</th><th>Date Décès</th><th>Zone</th><th>Cause</th><th class="text-center">Statut</th><th class="text-end">Détails</th></tr></thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="6" class="text-center p-4 text-muted">Vous n'avez encore soumis aucune déclaration.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $dec): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($dec['date_saisie']))) ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($dec['date_deces']))) ?></td>
                                        <td><?= htmlspecialchars($dec['nom_zone']) ?></td>
                                        <td><?= htmlspecialchars($dec['nom_cause']) ?></td>
                                        <td class="text-center">
                                            <?php
                                                $status = $dec['statut_validation'];
                                                if ($status == 'en_attente') echo '<span class="badge text-bg-warning">En attente</span>';
                                                elseif ($status == 'valide') echo '<span class="badge text-bg-success">Validé</span>';
                                                elseif ($status == 'rejete') echo '<span class="badge text-bg-danger">Rejeté</span>';
                                            ?>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="<?= $dec['id'] ?>"><i class="fas fa-eye"></i></button>
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
</div>

<div class="modal fade" id="historyDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content" id="historyDetailsModalContent"></div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- === SCRIPT JS INTÉGRÉ ET CORRIGÉ === -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const historyModal = new bootstrap.Modal(document.getElementById('historyDetailsModal'));
    const historyTable = document.getElementById('historyTable');
    
    if (historyTable) {
        historyTable.addEventListener('click', function(e) {
            const viewButton = e.target.closest('.btn-view-details');
            if (viewButton) {
                const declarationId = viewButton.dataset.id;
                const modalContent = document.getElementById('historyDetailsModalContent');
                modalContent.innerHTML = '<div class="modal-body text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
                historyModal.show();
                fetch(`api/get_declaration_details.php?id=${declarationId}`)
                    .then(response => response.ok ? response.text() : Promise.reject('Erreur de chargement des détails.'))
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        doc.querySelector('.modal-footer .btn-success')?.remove();
                        modalContent.innerHTML = doc.body.innerHTML;
                    })
                    .catch(error => {
                        modalContent.innerHTML = `<div class="modal-body"><div class="alert alert-danger">${error}</div></div>`;
                    });
            }
        });
    }

    const noDataHtml = '<div class="no-data-message"><p>Pas assez de données pour afficher ce graphique.</p></div>';

    function renderChart(containerId, options, data) {
        const container = document.querySelector(`#${containerId}`);
        if (container && Object.keys(data).length > 0) {
            new ApexCharts(container, options).render();
        } else if (container) {
            container.innerHTML = noDataHtml;
        }
    }

    // Données pour les graphiques, injectées par PHP
    const causesData = <?php echo json_encode($causes_data ?? []); ?>;
    const zonesData = <?php echo json_encode($zones_data ?? []); ?>;
    const agesData = <?php echo json_encode($ages_data ?? []); ?>;

    // Graphique des Causes
    const causesOptions = {
        series: Object.values(causesData),
        chart: { type: 'donut', height: 320 },
        labels: Object.keys(causesData),
        legend: { position: 'bottom' }
    };
    renderChart('causesChart', causesOptions, causesData);

    // Graphique des Zones
    const zonesOptions = {
        series: [{ name: 'Saisies', data: Object.values(zonesData) }],
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true }},
        xaxis: { categories: Object.keys(zonesData) },
        colors: ['#10b981']
    };
    renderChart('zonesChart', zonesOptions, zonesData);

    // Graphique des Âges
    const agesOptions = {
        series: Object.values(agesData),
        chart: { type: 'pie', height: 320 },
        labels: Object.keys(agesData),
        legend: { position: 'bottom' },
        colors: ['#f59e0b', '#ef4444', '#3b82f6', '#6b7280', '#10b981', '#ec4899', '#8b5cf6']
    };
    renderChart('agesChart', agesOptions, agesData);
});
</script>

</body>
</html>