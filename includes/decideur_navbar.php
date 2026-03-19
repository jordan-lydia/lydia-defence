<?php
// Fichier: includes/decideur_navbar.php
?>
<header class="navbar-top">
    <div class="d-flex align-items-center">
        <i class="fas fa-bars d-lg-none me-3" id="sidebar-toggle-mobile"></i>
    </div>
    <div class="navbar-top-right">
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="images/Ministre___biogr.webp" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2"><?= htmlspecialchars($_SESSION['user_nom_complet']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6><?= htmlspecialchars($_SESSION['user_nom_complet']) ?></h6>
                        <span><?= htmlspecialchars($_SESSION['user_role']) ?></span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="decideur_profile.php"><i class="fas fa-user-cog"></i><span>Mon Profil</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="logout.php"><i class="fas fa-sign-out-alt text-danger"></i><span class="text-danger">Déconnexion</span></a></li>
                </ul>
            </li>
        </ul>
    </div>
</header>