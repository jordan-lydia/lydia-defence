<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') { header('Location: connexion.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports et Statistiques - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/admin_navbar.php'; ?>
        
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Rapports et Statistiques Globales</h1>
                <p class="page-lead">Vue d'ensemble de toutes les données de mortalité validées.</p>
            </div>
            <a href="admin_generate_full_report.php" target="_blank" class="btn btn-danger btn-lg shadow-sm">
                <i class="fas fa-file-pdf me-2"></i>Télécharger le Rapport Général
            </a>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <div class="content-block">
                    <h5 class="content-block-header">Tendance des décès (6 derniers mois)</h5>
                    <div id="trendChart"></div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="content-block">
                    <h5 class="content-block-header">Top 5 des causes de décès (Global)</h5>
                    <div id="causesChart"></div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="content-block">
                    <h5 class="content-block-header">Répartition par Zone de Santé</h5>
                    <div id="zonesChart"></div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="content-block">
                    <h5 class="content-block-header">Pyramide des Âges (Décès par Sexe et Âge)</h5>
                    <div id="ageSexChart"></div>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="js/admin_reports_generator.js"></script>
</body>
</html>