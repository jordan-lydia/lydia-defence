<?php
session_start();
require_once 'includes/db.php';

// --- SÉCURITÉ : Vérifier si l'utilisateur est connecté et est un administrateur ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

// --- RÉCUPÉRATION DES DONNÉES DE LA BASE DE DONNÉES ---

// 1. Statistiques Clés (KPIs)
try {
    // Total des décès déclarés
    $stmt_total_deces = $pdo->query("SELECT COUNT(id) FROM declarations_deces");
    $total_deces = $stmt_total_deces->fetchColumn();

    // Nouvelles déclarations des dernières 24h
    $stmt_nouvelles_declarations = $pdo->query("SELECT COUNT(id) FROM declarations_deces WHERE date_saisie >= NOW() - INTERVAL 1 DAY");
    $nouvelles_declarations = $stmt_nouvelles_declarations->fetchColumn();

    // Total des utilisateurs actifs
    $stmt_total_utilisateurs = $pdo->query("SELECT COUNT(id) FROM utilisateurs WHERE statut_compte = 'actif'");
    $total_utilisateurs = $stmt_total_utilisateurs->fetchColumn();

    // Nombre de zones de santé
    $stmt_zones_couvertes = $pdo->query("SELECT COUNT(id) FROM zones_sante");
    $zones_couvertes = $stmt_zones_couvertes->fetchColumn();

} catch (PDOException $e) {
    // Gérer l'erreur si les requêtes échouent
    error_log("Erreur de récupération des KPIs: " . $e->getMessage());
    // Initialiser avec des valeurs par défaut pour éviter de casser la page
    $total_deces = $nouvelles_declarations = $total_utilisateurs = $zones_couvertes = 'Erreur';
}


// 2. Données pour le graphique "Répartition par Cause"
try {
    $sql_causes = "SELECT cd.nom_cause, COUNT(dd.id) as total 
                   FROM declarations_deces dd
                   JOIN causes_deces cd ON dd.cause_deces_id = cd.id
                   GROUP BY cd.nom_cause
                   ORDER BY total DESC
                   LIMIT 5";
    $stmt_causes = $pdo->query($sql_causes);
    $data_causes = $stmt_causes->fetchAll();

    // Préparer les données pour ApexCharts
    $labels_causes = [];
    $series_causes = [];
    foreach ($data_causes as $row) {
        $labels_causes[] = $row['nom_cause'];
        $series_causes[] = (int)$row['total'];
    }

} catch (PDOException $e) {
    error_log("Erreur de récupération des données pour le graphique des causes: " . $e->getMessage());
    $labels_causes = ['Erreur'];
    $series_causes = [1];
}


// 3. Données pour le graphique "Tendances des Décès" (Exemple : 12 derniers mois)
try {
    // Cette requête est plus complexe. Elle crée des "slots" pour chaque mois des 12 derniers mois
    // et compte les décès pour chaque slot.
    $sql_tendances = "
        SELECT DATE_FORMAT(m.month, '%b') as mois, COUNT(dd.id) as total
        FROM (
            SELECT DATE_FORMAT(CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) MONTH, '%Y-%m-01') AS month
            FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
            CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
            CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
        ) m
        LEFT JOIN declarations_deces dd ON DATE_FORMAT(dd.date_deces, '%Y-%m-01') = m.month
        WHERE m.month >= DATE_FORMAT(CURDATE() - INTERVAL 11 MONTH, '%Y-%m-01') AND m.month <= CURDATE()
        GROUP BY m.month
        ORDER BY m.month;
    ";
    $stmt_tendances = $pdo->query($sql_tendances);
    $data_tendances = $stmt_tendances->fetchAll();

    $labels_tendances = [];
    $series_tendances = [];
    foreach ($data_tendances as $row) {
        $labels_tendances[] = $row['mois'];
        $series_tendances[] = (int)$row['total'];
    }

} catch (PDOException $e) {
    error_log("Erreur de récupération des données pour le graphique des tendances: " . $e->getMessage());
    $labels_tendances = ['Erreur'];
    $series_tendances = [1];
}


// 4. Dernières déclarations
try {
    $sql_declarations_recentes = "
        SELECT 
            dd.date_deces, 
            zs.nom_zone, 
            cd.nom_cause,
            CONCAT(u.prenom, ' ', LEFT(u.nom, 1), '.') as nom_enqueteur,
            dd.statut_validation
        FROM declarations_deces dd
        JOIN zones_sante zs ON dd.zone_sante_id = zs.id
        JOIN causes_deces cd ON dd.cause_deces_id = cd.id
        JOIN utilisateurs u ON dd.enqueteur_id = u.id
        ORDER BY dd.date_saisie DESC
        LIMIT 5";
    $stmt_declarations_recentes = $pdo->query($sql_declarations_recentes);
    $declarations_recentes = $stmt_declarations_recentes->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur de récupération des dernières déclarations: " . $e->getMessage());
    $declarations_recentes = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Admin STDM</title>

    <!-- Dépendances CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">

    <div class="admin-wrapper">
        
        <?php include 'includes/admin_sidebar.php'; // Sidebar corrigée ?>

        <main class="content-wrapper">
            
            <?php include 'includes/admin_navbar.php'; ?>
            
            <div class="page-header mb-4">
                <h1 class="page-title">Tableau de Bord</h1>
                <p class="page-lead">Bienvenue, <?= htmlspecialchars($_SESSION['user_nom_complet']) ?> ! Voici un aperçu global de l'activité.</p>
            </div>

            <!-- Section des Statistiques Clés (KPI) - Données réelles -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="card-icon bg-primary-light"><i class="fas fa-cross"></i></div>
                        <div class="card-info">
                            <h6>Total Décès</h6>
                            <h5><?= is_numeric($total_deces) ? number_format($total_deces, 0, ',', ' ') : $total_deces ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="card-icon bg-danger-light"><i class="fas fa-file-import"></i></div>
                        <div class="card-info">
                            <h6>Nouvelles Déclarations</h6>
                            <h5><?= is_numeric($nouvelles_declarations) ? $nouvelles_declarations : $nouvelles_declarations ?> <small class="text-muted">(24h)</small></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="card-icon bg-success-light"><i class="fas fa-users"></i></div>
                        <div class="card-info">
                            <h6>Utilisateurs Actifs</h6>
                            <h5><?= is_numeric($total_utilisateurs) ? $total_utilisateurs : $total_utilisateurs ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="card-icon bg-warning-light"><i class="fas fa-map-marked-alt"></i></div>
                        <div class="card-info">
                            <h6>Zones Couvertes</h6>
                            <h5><?= is_numeric($zones_couvertes) ? $zones_couvertes : $zones_couvertes ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section des Graphiques -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="content-block">
                        <div class="content-block-header mb-3">
                            <h5>Tendances des Décès (12 derniers mois)</h5>
                        </div>
                        <div id="deathsOverTimeChart"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-block">
                        <div class="content-block-header mb-3">
                            <h5>Répartition par Cause (Top 5)</h5>
                        </div>
                        <div id="deathsByCauseChart"></div>
                    </div>
                </div>
            </div>

            <!-- Section des Raccourcis et Dernières activités -->
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="content-block">
                        <h5 class="content-block-header">Dernières Déclarations</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless table-hover">
                                <thead>
                                    <tr>
                                        <th>Date Décès</th><th>Zone</th><th>Cause</th><th>Enquêteur</th><th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($declarations_recentes)): ?>
                                        <tr><td colspan="5" class="text-center text-muted">Aucune déclaration récente.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($declarations_recentes as $declaration): ?>
                                            <tr>
                                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($declaration['date_deces']))) ?></td>
                                                <td><?= htmlspecialchars($declaration['nom_zone']) ?></td>
                                                <td><?= htmlspecialchars($declaration['nom_cause']) ?></td>
                                                <td><?= htmlspecialchars($declaration['nom_enqueteur']) ?></td>
                                                <td>
                                                    <?php
                                                        $status = $declaration['statut_validation'];
                                                        $badge_class = '';
                                                        if ($status == 'en_attente') $badge_class = 'bg-warning text-dark';
                                                        elseif ($status == 'valide') $badge_class = 'bg-success';
                                                        elseif ($status == 'rejete') $badge_class = 'bg-danger';
                                                    ?>
                                                    <span class="badge <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                     <div class="content-block">
                        <h5 class="content-block-header mb-3">Actions Rapides</h5>
                        <div class="d-grid gap-2">
                            <!-- LIENS FONCTIONNELS -->
                            <a href="admin_users_list.php" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i> Ajouter un utilisateur</a>
                            <a href="admin_reports_generator.php" class="btn btn-outline-secondary"><i class="fas fa-file-pdf me-2"></i> Générer un rapport</a>
                            <a href="admin_alerts_send.php" class="btn btn-outline-secondary"><i class="fas fa-broadcast-tower me-2"></i> Envoyer une alerte</a>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Dépendances JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- JS Personnalisé pour l'admin -->
    <script>
        // On passe les données PHP au JavaScript pour qu'il puisse construire les graphiques
        const labelsCauses = <?= json_encode($labels_causes) ?>;
        const seriesCauses = <?= json_encode($series_causes) ?>;
        const labelsTendances = <?= json_encode($labels_tendances) ?>;
        const seriesTendances = <?= json_encode($series_tendances) ?>;
    </script>
    <script src="js/admin_dashboard_charts.js"></script> <!-- Nouveau nom de fichier JS -->
    <script src="js/admin_notifications.js"></script>
</body>
</html>