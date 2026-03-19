<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

$response = ['success' => false];

try {
    $base_condition = "WHERE dd.statut_validation = 'valide'";
    
    // 1. Tendance des 6 derniers mois (Jan-Juin 2025)
    $sql_trend = "SELECT DATE_FORMAT(date_deces, '%b %Y') as mois, COUNT(id) as total FROM declarations_deces dd $base_condition AND date_deces BETWEEN '2025-01-01' AND '2025-06-30' GROUP BY mois ORDER BY date_deces ASC";
    $stmt_trend = $pdo->query($sql_trend);
    $trend_data = $stmt_trend->fetchAll(PDO::FETCH_KEY_PAIR);
    $response['trendChart'] = [ 'labels' => array_keys($trend_data), 'series' => array_values(array_map('intval', $trend_data)) ];

    // 2. Top 5 des causes
    $sql_causes = "SELECT cd.nom_cause, COUNT(dd.id) as total FROM declarations_deces dd JOIN causes_deces cd ON dd.cause_deces_id = cd.id $base_condition GROUP BY cd.nom_cause ORDER BY total DESC LIMIT 5";
    $stmt_causes = $pdo->query($sql_causes);
    $causes_data = $stmt_causes->fetchAll(PDO::FETCH_KEY_PAIR);
    $response['causesChart'] = [ 'labels' => array_keys($causes_data), 'series' => array_values(array_map('intval', $causes_data)) ];
    
    // 3. Répartition par Zone
    $sql_zones = "SELECT zs.nom_zone, COUNT(dd.id) as total FROM declarations_deces dd JOIN zones_sante zs ON dd.zone_sante_id = zs.id $base_condition GROUP BY zs.nom_zone ORDER BY total DESC";
    $stmt_zones = $pdo->query($sql_zones);
    $zones_data = $stmt_zones->fetchAll(PDO::FETCH_KEY_PAIR);
    $response['zonesChart'] = [ 'labels' => array_keys($zones_data), 'series' => array_values(array_map('intval', $zones_data)) ];
    
    // 4. Pyramide des âges
    $sql_age_sex = "
        SELECT 
            CASE 
                WHEN age_annees BETWEEN 0 AND 4 THEN '0-4'
                WHEN age_annees BETWEEN 5 AND 14 THEN '5-14'
                WHEN age_annees BETWEEN 15 AND 29 THEN '15-29'
                WHEN age_annees BETWEEN 30 AND 44 THEN '30-44'
                WHEN age_annees BETWEEN 45 AND 59 THEN '45-59'
                WHEN age_annees BETWEEN 60 AND 74 THEN '60-74'
                ELSE '75+'
            END as age_group,
            SUM(CASE WHEN sexe = 'M' THEN 1 ELSE 0 END) as hommes,
            SUM(CASE WHEN sexe = 'F' THEN 1 ELSE 0 END) * -1 as femmes
        FROM declarations_deces dd
        $base_condition
        GROUP BY age_group
        ORDER BY age_group ASC
    ";
    $stmt_age_sex = $pdo->query($sql_age_sex);
    $age_sex_data = $stmt_age_sex->fetchAll();
    $response['ageSexChart'] = [
        'labels' => array_column($age_sex_data, 'age_group'),
        'hommes' => array_map('intval', array_column($age_sex_data, 'hommes')),
        'femmes' => array_map('intval', array_column($age_sex_data, 'femmes'))
    ];

    $response['success'] = true;
    
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);