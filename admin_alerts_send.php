<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

try {
    // Récupérer les rôles pour le menu déroulant de ciblage
    $roles = $pdo->query("SELECT id, nom_role FROM roles ORDER BY nom_role ASC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur de récupération des rôles: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer une Alerte - Admin STDM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_alerts_send.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/admin_navbar.php'; ?>
        
        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Centre de Communication</h1>
                <p class="page-lead">Envoyez des notifications et des alertes ciblées aux utilisateurs de la plateforme.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="content-block">
                    <form id="alertForm">
                        <div class="mb-3">
                            <label class="form-label">Type d'Alerte</label>
                            <div id="alertTypeSelector" class="d-flex flex-wrap gap-2">
                                <label class="alert-type-label">
                                    <input type="radio" name="alert_type" value="info" class="d-none" checked>
                                    <div class="alert-type-card bg-info-light"><i class="fas fa-info-circle"></i> Information</div>
                                </label>
                                <label class="alert-type-label">
                                    <input type="radio" name="alert_type" value="success" class="d-none">
                                    <div class="alert-type-card bg-success-light"><i class="fas fa-check-circle"></i> Succès</div>
                                </label>
                                <label class="alert-type-label">
                                    <input type="radio" name="alert_type" value="warning" class="d-none">
                                    <div class="alert-type-card bg-warning-light"><i class="fas fa-exclamation-triangle"></i> Avertissement</div>
                                </label>
                                <label class="alert-type-label">
                                    <input type="radio" name="alert_type" value="danger" class="d-none">
                                    <div class="alert-type-card bg-danger-light"><i class="fas fa-skull-crossbones"></i> Danger</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="target" class="form-label">Cible de l'Alerte</label>
                            <select id="target" name="target" class="form-select" required>
                                <option value="all">Tous les Utilisateurs</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="role_<?= $role['id'] ?>">Rôle : <?= htmlspecialchars($role['nom_role']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message de l'Alerte</label>
                            <textarea id="message" name="message" class="form-control" rows="5" placeholder="Saisissez votre message ici..." required></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer l'Alerte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/admin_alerts_send.js"></script>
</body>
</html>