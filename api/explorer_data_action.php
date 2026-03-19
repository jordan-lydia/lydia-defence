<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Gestionnaire', 'Administrateur'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

$response = ['success' => false];
$params = [];
$where_clauses = ["dd.statut_validation = 'valide'"];

// Construction dynamique des clauses WHERE
if (!empty($_GET['date_start'])) { $where_clauses[] = "dd.date_deces >= ?"; $params[] = $_GET['date_start']; }
if (!empty($_GET['date_end'])) { $where_clauses[] = "dd.date_deces <= ?"; $params[] = $_GET['date_end']; }
if (!empty($_GET['zone_id'])) { $where_clauses[] = "dd.zone_sante_id = ?"; $params[] = $_GET['zone_id']; }
if (!empty($_GET['cause_id'])) { $where_clauses[] = "dd.cause_deces_id = ?"; $params[] = $_GET['cause_id']; }
if (!empty($_GET['sexe'])) { $where_clauses[] = "dd.sexe = ?"; $params[] = $_GET['sexe']; }
if (!empty($_GET['age_range'])) {
    list($min, $max) = explode('-', $_GET['age_range']);
    if($max === '') { $where_clauses[] = "dd.age_annees >= ?"; $params[] = $min; }
    else { $where_clauses[] = "dd.age_annees BETWEEN ? AND ?"; $params[] = $min; $params[] = $max; }
}

$where_sql = "WHERE " . implode(' AND ', $where_clauses);

try {
    // 1. Calcul des KPIs
    $sql_kpi = "SELECT COUNT(id) as total_deces, AVG(age_annees) as age_moyen FROM declarations_deces dd $where_sql";
    $stmt_kpi = $pdo->prepare($sql_kpi);
    $stmt_kpi->execute($params);
    $kpi_data = $stmt_kpi->fetch();
    $response['kpis'] = [
        'total_deces' => (int) $kpi_data['total_deces'],
        'age_moyen' => round($kpi_data['age_moyen'] ?? 0, 1)
    ];

    // 2. Données pour le graphique (Top 5 causes pour la sélection)
    $sql_chart = "SELECT cd.nom_cause, COUNT(dd.id) as total FROM declarations_deces dd JOIN causes_deces cd ON dd.cause_deces_id = cd.id $where_sql GROUP BY cd.nom_cause ORDER BY total DESC LIMIT 5";
    $stmt_chart = $pdo->prepare($sql_chart);
    $stmt_chart->execute($params);
    $chart_data = $stmt_chart->fetchAll(PDO::FETCH_KEY_PAIR);
    $response['chartData'] = [
        'labels' => array_keys($chart_data),
        'series' => array_values(array_map('intval', $chart_data))
    ];

    // 3. Données brutes pour la table
    $sql_table = "SELECT dd.date_deces, dd.age_annees, dd.sexe, zs.nom_zone, cd.nom_cause FROM declarations_deces dd JOIN zones_sante zs ON dd.zone_sante_id = zs.id JOIN causes_deces cd ON dd.cause_deces_id = cd.id $where_sql ORDER BY dd.date_deces DESC LIMIT 100";
    $stmt_table = $pdo->prepare($sql_table);
    $stmt_table->execute($params);
    $response['tableData'] = $stmt_table->fetchAll();

    $response['success'] = true;
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>