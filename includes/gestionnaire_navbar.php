<?php
// Fichier: includes/gestionnaire_navbar.php

// Si la connexion PDO n'est pas déjà disponible, on l'inclut.
if (!isset($pdo)) {
    require_once __DIR__ . '/db.php';
}

$user_id = $_SESSION['user_id'] ?? 0;
$unread_count = 0;
$notifications = [];

if ($user_id > 0) {
    try {
        // Pour un gestionnaire, on peut fusionner les notifications système et les déclarations en attente
        // Ici, on va se concentrer sur les notifications des déclarations en attente
        
        // 1. Compter toutes les déclarations en attente
        $stmt_count = $pdo->query("SELECT COUNT(id) FROM declarations_deces WHERE statut_validation = 'en_attente'");
        $unread_count = $stmt_count->fetchColumn();

        // 2. Récupérer les 5 plus anciennes déclarations en attente pour les afficher
        $sql_notifs = "
            SELECT 
                dd.id, dd.date_saisie, u.prenom, u.nom
            FROM declarations_deces dd
            JOIN utilisateurs u ON dd.enqueteur_id = u.id
            WHERE dd.statut_validation = 'en_attente'
            ORDER BY dd.date_saisie ASC
            LIMIT 5";
        $stmt_notifs = $pdo->query($sql_notifs);
        $notifications = $stmt_notifs->fetchAll();

    } catch (PDOException $e) {
        error_log("Erreur de récupération des notifications pour le gestionnaire: " . $e->getMessage());
    }
}
?>
<header class="navbar-top">
    <div class="d-flex align-items-center">
        <i class="fas fa-bars d-lg-none me-3" id="sidebar-toggle-mobile"></i>
    </div>
    <div class="navbar-top-right">
        <ul class="navbar-nav">
            <!-- Notifications Dropdown (dynamique pour le gestionnaire) -->
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-warning badge-number" id="notification-badge"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications" aria-labelledby="notificationsDropdown">
                    <li class="dropdown-header">
                        Vous avez <?= $unread_count ?> déclaration<?= ($unread_count > 1) ? 's' : '' ?> à valider
                        <a href="gestionnaire_declarations_pending.php"><span class="badge rounded-pill bg-primary p-2 ms-2">Tout voir</span></a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    <?php if (empty($notifications)): ?>
                        <li class="text-center p-3 text-muted">Aucune déclaration en attente</li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <a href="gestionnaire_declarations_validate.php?id=<?= $notif['id'] ?>" class="dropdown-item notification-item">
                                <i class="fas fa-file-alt text-warning"></i>
                                <div>
                                    <p class="mb-0">Nouvelle déclaration de <?= htmlspecialchars($notif['prenom'] . ' ' . $notif['nom']) ?></p>
                                    <small class="text-muted">Soumis le <?= htmlspecialchars(date('d/m/Y', strtotime($notif['date_saisie']))) ?></small>
                                </div>
                            </a>
                        <li><hr class="dropdown-divider"></li>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <li class="dropdown-footer">
                        <a href="gestionnaire_declarations_pending.php">Voir toutes les déclarations en attente</a>
                    </li>
                </ul>
            </li>
            <!-- User Profile Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="images/7590876.webp" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2"><?= htmlspecialchars($_SESSION['user_nom_complet']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6><?= htmlspecialchars($_SESSION['user_nom_complet']) ?></h6>
                        <span><?= htmlspecialchars($_SESSION['user_role']) ?></span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="gestionnaire_profile.php"><i class="fas fa-user-cog"></i><span>Mon Profil</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="logout.php"><i class="fas fa-sign-out-alt text-danger"></i><span class="text-danger">Déconnexion</span></a></li>
                </ul>
            </li>
        </ul>
    </div>
</header>