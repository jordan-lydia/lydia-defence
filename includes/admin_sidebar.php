<?php
// Fichier: includes/admin_sidebar.php

// Détermine la page active pour appliquer le style 'active'
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="admin_dashboard.php" class="sidebar-logo">
            <!-- Tu peux remplacer l'icône par une image SVG ou PNG de ton logo -->
            <i class="fas fa-shield-virus"></i>
            <span>STDM Admin</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="sidebar-title">Principal</li>

            <li class="<?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">
                <a href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt fa-fw"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>

            <li class="sidebar-title">Gestion des Données</li>
            
            <li class="<?= ($current_page == 'admin_declarations_list.php' || $current_page == 'admin_declarations_validate.php') ? 'active' : '' ?>">
                <a href="admin_declarations_list.php">
                    <i class="fas fa-file-medical-alt fa-fw"></i>
                    <span>Toutes les Déclarations</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'admin_declarations_pending.php') ? 'active' : '' ?>">
                <a href="admin_declarations_pending.php">
                    <i class="fas fa-hourglass-half fa-fw"></i>
                    <span>Déclarations en Attente</span>
                    <!-- Ce compteur peut être alimenté par une requête SQL -->
                    <span class="badge bg-warning ms-auto">5</span>
                </a>
            </li>

            <li class="sidebar-title">Administration</li>

            <li class="<?= ($current_page == 'admin_users_list.php' || $current_page == 'admin_users_add.php') ? 'active' : '' ?>">
                <a href="admin_users_list.php">
                    <i class="fas fa-users-cog fa-fw"></i>
                    <span>Gestion des Utilisateurs</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'admin_zones_list.php') ? 'active' : '' ?>">
                <a href="admin_zones_list.php">
                    <i class="fas fa-map-marked-alt fa-fw"></i>
                    <span>Zones de Santé</span>
                </a>
            </li>

            <li class="<?= ($current_page == 'admin_causes_list.php') ? 'active' : '' ?>">
                <a href="admin_causes_list.php">
                    <i class="fas fa-notes-medical fa-fw"></i>
                    <span>Causes de Décès</span>
                </a>
            </li>

            <li class="sidebar-title">Outils & Rapports</li>

            <li class="<?= ($current_page == 'admin_reports_generator.php') ? 'active' : '' ?>">
                <a href="admin_reports_generator.php">
                    <i class="fas fa-file-pdf fa-fw"></i>
                    <span>Générateur de Rapports</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'admin_alerts_send.php') ? 'active' : '' ?>">
                <a href="admin_alerts_send.php">
                    <i class="fas fa-broadcast-tower fa-fw"></i>
                    <span>Envoyer une Alerte</span>
                </a>
            </li>

            <li class="<?= ($current_page == 'admin_system_logs.php') ? 'active' : '' ?>">
                <a href="admin_system_logs.php">
                    <i class="fas fa-clipboard-list fa-fw"></i>
                    <span>Logs du Système</span>
                </a>
            </li>
            
            <li class="sidebar-title">Compte</li>

            <li class="<?= ($current_page == 'admin_profile.php') ? 'active' : '' ?>">
                <a href="admin_profile.php">
                    <i class="fas fa-user-circle fa-fw"></i>
                    <span>Mon Profil</span>
                </a>
            </li>

            <!-- Le footer est une bonne pratique pour le bouton de déconnexion -->
            <li class="sidebar-footer">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt fa-fw text-danger"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>