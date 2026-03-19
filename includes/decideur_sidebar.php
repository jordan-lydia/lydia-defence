<?php
// Fichier: includes/decideur_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="decideur_dashboard.php" class="sidebar-logo">
            <i class="fas fa-bullseye"></i>
            <span>STDM Décideur</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="sidebar-title">Vues Stratégiques</li>
            <li class="<?= ($current_page == 'decideur_dashboard.php') ? 'active' : '' ?>">
                <a href="decideur_dashboard.php"><i class="fas fa-chart-pie fa-fw"></i><span>Dashboard Global</span></a>
            </li>
            <li class="<?= ($current_page == 'decideur_map_view.php') ? 'active' : '' ?>">
                <a href="decideur_map_view.php"><i class="fas fa-map-marked-alt fa-fw"></i><span>Vue Cartographique</span></a>
            </li>
            <li class="<?= ($current_page == 'decideur_reports_view.php') ? 'active' : '' ?>">
                <a href="decideur_reports_view.php"><i class="fas fa-file-download fa-fw"></i><span>Consulter les Rapports</span></a>
            </li>
            <li class="sidebar-title">Compte</li>
            <li class="<?= ($current_page == 'decideur_profile.php') ? 'active' : '' ?>">
                <a href="decideur_profile.php"><i class="fas fa-user-circle fa-fw"></i><span>Mon Profil</span></a>
            </li>
            <li class="sidebar-footer">
                <a href="logout.php"><i class="fas fa-sign-out-alt fa-fw text-danger"></i><span>Déconnexion</span></a>
            </li>
        </ul>
    </nav>
</aside>