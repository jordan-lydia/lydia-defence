<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

try {
    $stmt = $pdo->query("SELECT * FROM causes_deces ORDER BY categorie ASC, nom_cause ASC");
    $causes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Causes de Décès - Admin</title>
    
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
                <h1 class="page-title">Gestion des Causes de Décès</h1>
                <p class="page-lead">Standardisez les données en gérant la liste officielle des causes de décès.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#causeModal">
                <i class="fas fa-plus-circle me-2"></i>Ajouter une Cause
            </button>
        </div>
        
        <div class="content-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="causesTable">
                    <thead>
                        <tr>
                            <th>Nom de la Cause</th>
                            <th>Catégorie</th>
                            <th>Code (CIM-10)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($causes as $cause): ?>
                        <tr id="cause-row-<?= $cause['id'] ?>">
                            <td class="cause-name"><?= htmlspecialchars($cause['nom_cause']) ?></td>
                            <td class="cause-categorie"><?= htmlspecialchars($cause['categorie']) ?></td>
                            <td class="cause-code"><span class="badge text-bg-secondary"><?= htmlspecialchars($cause['code_cim10']) ?></span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary btn-edit-cause"
                                        data-cause-id="<?= $cause['id'] ?>"
                                        data-cause-nom="<?= htmlspecialchars($cause['nom_cause']) ?>"
                                        data-cause-categorie="<?= htmlspecialchars($cause['categorie']) ?>"
                                        data-cause-code="<?= htmlspecialchars($cause['code_cim10']) ?>"
                                        title="Modifier"><i class="fas fa-pencil-alt"></i></button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-cause"
                                        data-cause-id="<?= $cause['id'] ?>" 
                                        data-cause-name="<?= htmlspecialchars($cause['nom_cause']) ?>" 
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

<!-- MODAL POUR AJOUTER/MODIFIER UNE CAUSE -->
<div class="modal fade" id="causeModal" tabindex="-1" aria-labelledby="causeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="causeModalLabel">Ajouter une Cause de Décès</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="causeForm">
                    <input type="hidden" name="cause_id" id="cause_id">
                    <div class="mb-3">
                        <label for="nom_cause" class="form-label">Nom de la Cause</label>
                        <input type="text" class="form-control" id="nom_cause" name="nom_cause" required>
                    </div>
                    <div class="mb-3">
                        <label for="categorie" class="form-label">Catégorie</label>
                        <input type="text" class="form-control" id="categorie" name="categorie" placeholder="Ex: Maladies infectieuses">
                    </div>
                    <div class="mb-3">
                        <label for="code_cim10" class="form-label">Code CIM-10 (Optionnel)</label>
                        <input type="text" class="form-control" id="code_cim10" name="code_cim10" placeholder="Ex: B50-B54">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary" form="causeForm">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/admin_causes_list.js"></script>
</body>
</html>