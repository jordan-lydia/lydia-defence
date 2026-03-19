<?php
// Fichier: includes/gestionnaire_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="gestionnaire_dashboard.php" class="sidebar-logo">
            <i class="fas fa-tasks"></i>
            <span>STDM Gestionnaire</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="sidebar-title">Principal</li>
            <li class="<?= ($current_page == 'gestionnaire_dashboard.php') ? 'active' : '' ?>">
                <a href="gestionnaire_dashboard.php"><i class="fas fa-tachometer-alt fa-fw"></i><span>Tableau de Bord</span></a>
            </li>
            <li class="sidebar-title">Validation des Données</li>
            <li class="<?= ($current_page == 'gestionnaire_declarations_pending.php') ? 'active' : '' ?>">
                <a href="gestionnaire_declarations_pending.php">
                    <i class="fas fa-hourglass-half fa-fw"></i><span>Déclarations à Valider</span>
                    <span class="badge bg-warning ms-auto">5</span>
                </a>
            </li>
            <li class="<?= ($current_page == 'gestionnaire_declarations_history.php') ? 'active' : '' ?>">
                <a href="gestionnaire_declarations_history.php"><i class="fas fa-history fa-fw"></i><span>Historique des Validations</span></a>
            </li>
            <li class="sidebar-title">Analyse & Rapports</li>
            <li class="<?= ($current_page == 'gestionnaire_data_explorer.php') ? 'active' : '' ?>">
                <a href="gestionnaire_data_explorer.php"><i class="fas fa-chart-bar fa-fw"></i><span>Explorateur de Données</span></a>
            </li>
            <li class="<?= ($current_page == 'gestionnaire_reports.php') ? 'active' : '' ?>">
                <a href="gestionnaire_reports.php"><i class="fas fa-file-pdf fa-fw"></i><span>Générer un Rapport</span></a>
            </li>
            <li class="sidebar-title">Compte</li>
            <li class="<?= ($current_page == 'gestionnaire_profile.php') ? 'active' : '' ?>">
                <a href="gestionnaire_profile.php"><i class="fas fa-user-circle fa-fw"></i><span>Mon Profil</span></a>
            </li>
            <li class="sidebar-footer">
                <a href="logout.php"><i class="fas fa-sign-out-alt fa-fw text-danger"></i><span>Déconnexion</span></a>
            </li>
        </ul>
    </nav>
</aside>