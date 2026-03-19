<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Décideur') {
    header('Location: connexion.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulter les Rapports - Décideur</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="css/admine.css">
    <link rel="stylesheet" href="css/decideur.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/decideur_sidebar.php'; ?>
    <div class="page-container">
        <?php include 'includes/decideur_navbar.php'; ?>
        <main class="page-content">
            
            <div class="page-header mb-4">
                <h1 class="page-title">Bibliothèque de Rapports</h1>
                <p class="page-lead">Téléchargez les rapports consolidés pour vos analyses et présentations.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="report-card-download">
                        <div class="card-icon-report"><i class="fas fa-calendar-day"></i></div>
                        <div class="card-body-report">
                            <h5 class="card-title-report">Rapport du Jour</h5>
                            <p class="card-text-report">Un résumé de l'activité des dernières 24 heures.</p>
                            <a href="admin_generate_custom_report.php?report_type=daily" target="_blank" class="btn btn-danger"><i class="fas fa-download me-2"></i>Télécharger</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="report-card-download">
                        <div class="card-icon-report"><i class="fas fa-calendar-week"></i></div>
                        <div class="card-body-report">
                            <h5 class="card-title-report">Rapport Hebdomadaire</h5>
                            <p class="card-text-report">Analyse des 7 derniers jours.</p>
                            <a href="admin_generate_custom_report.php?report_type=weekly" target="_blank" class="btn btn-danger"><i class="fas fa-download me-2"></i>Télécharger</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="report-card-download">
                        <div class="card-icon-report"><i class="fas fa-calendar-alt"></i></div>
                        <div class="card-body-report">
                            <h5 class="card-title-report">Rapport Mensuel</h5>
                            <p class="card-text-report">Rapport complet du mois précédent.</p>
                            <a href="admin_generate_custom_report.php?report_type=monthly_summary" target="_blank" class="btn btn-danger"><i class="fas fa-download me-2"></i>Télécharger</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="report-card-download">
                        <div class="card-icon-report"><i class="fas fa-chart-line"></i></div>
                        <div class="card-body-report">
                            <h5 class="card-title-report">Rapport Annuel</h5>
                            <p class="card-text-report">Synthèse de l'année en cours.</p>
                            <a href="admin_generate_custom_report.php?report_type=annual" target="_blank" class="btn btn-danger"><i class="fas fa-download me-2"></i>Télécharger</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="report-card-download">
                        <div class="card-icon-report"><i class="fas fa-globe-africa"></i></div>
                        <div class="card-body-report">
                            <h5 class="card-title-report">Rapport Général Complet</h5>
                            <p class="card-text-report">Toutes les données validées. Attention : fichier volumineux.</p>
                            <a href="admin_generate_custom_report.php?report_type=full" target="_blank" class="btn btn-danger"><i class="fas fa-download me-2"></i>Télécharger</a>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>