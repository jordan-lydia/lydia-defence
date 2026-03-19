<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$admin_id]);
    $all_notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération des notifications: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toutes les Notifications - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_notifications.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/admin_navbar.php'; ?>
        
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Centre de Notifications</h1>
                <p class="page-lead">Historique de toutes les notifications reçues.</p>
            </div>
            <button class="btn btn-primary" id="markAllAsReadBtn"><i class="fas fa-check-double me-2"></i>Marquer tout comme lu</button>
        </div>
        
        <div class="content-block">
            <ul class="list-group list-group-flush" id="notificationList">
                <?php if (empty($all_notifications)): ?>
                    <li class="list-group-item text-center p-4 text-muted">Vous n'avez aucune notification.</li>
                <?php else: ?>
                    <?php foreach ($all_notifications as $notif): ?>
                        <li class="list-group-item notification-entry <?= $notif['is_read'] ? 'read' : 'unread' ?>" data-notif-id="<?= $notif['id'] ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="notification-icon me-3"><i class="<?= htmlspecialchars($notif['icon']) ?> text-<?= htmlspecialchars($notif['type']) ?>"></i></span>
                                    <div>
                                        <p class="mb-0 message"><?= htmlspecialchars($notif['message']) ?></p>
                                        <small class="text-muted"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($notif['created_at']))) ?></small>
                                    </div>
                                </div>
                                <?php if (!$notif['is_read']): ?>
                                    <button class="btn btn-sm btn-outline-primary mark-as-read-btn" title="Marquer comme lu"><i class="fas fa-check"></i></button>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/admin_notifications.js"></script>
</body>
</html>