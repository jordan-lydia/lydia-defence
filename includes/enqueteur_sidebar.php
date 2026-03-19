<?php
// Fichier: includes/enqueteur_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="enqueteur_dashboard.php" class="sidebar-logo">
            <i class="fas fa-user-edit"></i>
            <span>STDM Enquêteur</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="sidebar-title">Actions</li>
            <li class="<?= ($current_page == 'enqueteur_dashboard.php') ? 'active' : '' ?>">
                <a href="enqueteur_dashboard.php">
                    <i class="fas fa-keyboard fa-fw"></i>
                    <span>Nouvelle Déclaration</span>
                </a>
            </li>
            <li class="<?= ($current_page == 'enqueteur_history.php') ? 'active' : '' ?>">
                <a href="enqueteur_history.php">
                    <i class="fas fa-history fa-fw"></i>
                    <span>Mes Saisies</span>
                </a>
            </li>
            <li class="sidebar-title">Compte</li>
            <li class="<?= ($current_page == 'enqueteur_profile.php') ? 'active' : '' ?>">
                <a href="enqueteur_profile.php">
                    <i class="fas fa-user-circle fa-fw"></i>
                    <span>Mon Profil</span>
                </a>
            </li>
            <li class="sidebar-footer">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt fa-fw text-danger"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>