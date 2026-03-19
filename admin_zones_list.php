<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

try {
    $stmt = $pdo->query("SELECT * FROM zones_sante ORDER BY nom_zone ASC");
    $zones = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Zones de Santé - Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/admin_navbar.php'; ?>
        
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Gestion des Zones de Santé</h1>
                <p class="page-lead">Ajoutez, modifiez ou supprimez les zones de santé couvertes.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#zoneModal">
                <i class="fas fa-plus-circle me-2"></i>Ajouter une Zone
            </button>
        </div>
        
        <div class="content-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="zonesTable">
                    <thead>
                        <tr>
                            <th>Nom de la Zone</th>
                            <th>Commune</th>
                            <th>Province</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($zones as $zone): ?>
                        <tr id="zone-row-<?= $zone['id'] ?>">
                            <td class="zone-name"><?= htmlspecialchars($zone['nom_zone']) ?></td>
                            <td class="zone-commune"><?= htmlspecialchars($zone['commune']) ?></td>
                            <td class="zone-province"><?= htmlspecialchars($zone['province']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary btn-edit-zone"
                                        data-zone-id="<?= $zone['id'] ?>"
                                        data-zone-nom="<?= htmlspecialchars($zone['nom_zone']) ?>"
                                        data-zone-commune="<?= htmlspecialchars($zone['commune']) ?>"
                                        title="Modifier"><i class="fas fa-pencil-alt"></i></button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-zone"
                                        data-zone-id="<?= $zone['id'] ?>" 
                                        data-zone-name="<?= htmlspecialchars($zone['nom_zone']) ?>" 
                                        title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- MODAL POUR AJOUTER/MODIFIER UNE ZONE -->
<div class="modal fade" id="zoneModal" tabindex="-1" aria-labelledby="zoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="zoneModalLabel">Ajouter une Zone de Santé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="zoneForm">
                    <input type="hidden" name="zone_id" id="zone_id">
                    <div class="mb-3">
                        <label for="nom_zone" class="form-label">Nom de la Zone</label>
                        <input type="text" class="form-control" id="nom_zone" name="nom_zone" required>
                    </div>
                    <div class="mb-3">
                        <label for="commune" class="form-label">Commune</label>
                        <input type="text" class="form-control" id="commune" name="commune">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary" form="zoneForm">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/admin_zones_list.js"></script>
</body>
</html>