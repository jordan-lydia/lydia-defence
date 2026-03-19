<?php
// Fichier: includes/admin_navbar.php

// Si la connexion PDO n'est pas déjà disponible, on l'inclut.
// C'est une sécurité pour rendre ce composant plus autonome.
if (!isset($pdo)) {
    require_once __DIR__ . '/db.php';
}

$user_id = $_SESSION['user_id'] ?? 0; // Utiliser l'ID de l'utilisateur en session
$unread_count = 0;
$notifications = [];

if ($user_id > 0) {
    try {
        // Compter les notifications non lues pour l'utilisateur connecté
        $stmt_count = $pdo->prepare("SELECT COUNT(id) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt_count->execute([$user_id]);
        $unread_count = $stmt_count->fetchColumn();

        // Récupérer les 5 dernières notifications pour l'affichage dans le dropdown
        $stmt_notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt_notifs->execute([$user_id]);
        $notifications = $stmt_notifs->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur de récupération des notifications: " . $e->getMessage());
        // On ne bloque pas la page, on continue avec des données vides.
    }
}
?>
<header class="navbar-top">
    <!-- Côté gauche de la navbar -->
    <div class="d-flex align-items-center">
        <!-- Icône pour ouvrir/fermer la sidebar sur les appareils mobiles -->
        <i class="fas fa-bars d-lg-none me-3" id="sidebar-toggle-mobile"></i>
        
        <!-- Formulaire de recherche -->
        <form class="search-form d-none d-md-block">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Rechercher...">
                <button class="btn" type="button"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>

    <!-- Côté droit de la navbar -->
    <div class="navbar-top-right">
        <ul class="navbar-nav">
            <!-- Dropdown de Notifications -->
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger badge-number" id="notification-badge"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications" aria-labelledby="notificationsDropdown">
                    <li class="dropdown-header">
                        Vous avez <?= $unread_count ?> nouvelle<?= ($unread_count > 1) ? 's' : '' ?> notification<?= ($unread_count > 1) ? 's' : '' ?>
                        <a href="admin_notifications.php"><span class="badge rounded-pill bg-primary p-2 ms-2">Tout voir</span></a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    <?php if (empty($notifications)): ?>
                        <li class="text-center p-3 text-muted">Aucune notification</li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <a href="<?= htmlspecialchars($notif['link'] ?? '#') ?>" class="dropdown-item notification-item <?= $notif['is_read'] ? '' : 'notification-unread' ?>">
                                <i class="<?= htmlspecialchars($notif['icon']) ?> text-<?= htmlspecialchars($notif['type']) ?>"></i>
                                <div>
                                    <p class="mb-0"><?= htmlspecialchars($notif['message']) ?></p>
                                    <small class="text-muted"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($notif['created_at']))) ?></small>
                                </div>
                            </a>
                        <li><hr class="dropdown-divider"></li>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <li class="dropdown-footer">
                        <a href="admin_notifications.php">Voir toutes les notifications</a>
                    </li>
                </ul>
            </li>

            <!-- Dropdown du Profil Utilisateur -->
            <li class="nav-item dropdown">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="images/360_F_644857382_DYcfuCz7SEVBLejwX9YQ0UUNPvD8B9Rs.jpg" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2"><?= htmlspecialchars($_SESSION['user_nom_complet']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6><?= htmlspecialchars($_SESSION['user_nom_complet']) ?></h6>
                        <span><?= htmlspecialchars($_SESSION['user_role']) ?></span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="admin_profile.php"><i class="fas fa-user-cog"></i><span>Mon Profil</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="logout.php"><i class="fas fa-sign-out-alt text-danger"></i><span class="text-danger">Déconnexion</span></a></li>
                </ul>
            </li>
        </ul>
    </div>
</header>