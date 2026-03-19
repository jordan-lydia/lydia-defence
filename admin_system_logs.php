<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

try {
    $sql = "
        SELECT 
            sl.id, sl.level, sl.action, sl.ip_address, sl.created_at,
            u.prenom, u.nom
        FROM system_logs sl
        LEFT JOIN utilisateurs u ON sl.user_id = u.id
        ORDER BY sl.created_at DESC";
    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération des logs: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs du Système - Admin STDM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_system_logs.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/admin_navbar.php'; ?>
        
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Journal des Événements du Système</h1>
                <p class="page-lead">Suivi des actions importantes effectuées sur la plateforme.</p>
            </div>
        </div>
        
        <div class="content-block">
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="logsTable">
                    <thead>
                        <tr>
                            <th>Niveau</th>
                            <th>Action</th>
                            <th>Utilisateur</th>
                            <th>Adresse IP</th>
                            <th>Date & Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="5" class="text-center text-muted p-4">Aucun log système enregistré.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <?php
                                        $level = $log['level'];
                                        $badge_class = 'bg-secondary';
                                        if ($level == 'INFO') $badge_class = 'bg-info text-dark';
                                        elseif ($level == 'WARNING') $badge_class = 'bg-warning text-dark';
                                        elseif ($level == 'DANGER') $badge_class = 'bg-danger';
                                        elseif ($level == 'AUTH') $badge_class = 'bg-primary';
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= $level ?></span>
                                </td>
                                <td class="action-message"><?= htmlspecialchars($log['action']) ?></td>
                                <td><?= $log['prenom'] ? htmlspecialchars($log['prenom'] . ' ' . $log['nom']) : '<em>Système</em>' ?></td>
                                <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($log['created_at']))) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>