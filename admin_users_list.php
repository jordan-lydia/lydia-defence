<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

try {
    $users_stmt = $pdo->query("SELECT u.id, u.nom, u.prenom, u.email, u.statut_compte, u.role_id, r.nom_role FROM utilisateurs u JOIN roles r ON u.role_id = r.id ORDER BY u.nom ASC, u.prenom ASC");
    $users = $users_stmt->fetchAll();
    
    $roles_stmt = $pdo->query("SELECT id, nom_role FROM roles ORDER BY nom_role ASC");
    $roles = $roles_stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Admin STDM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_users_list.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/admin_navbar.php'; ?>
        
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Gestion des Utilisateurs</h1>
                <p class="page-lead">Gérez les utilisateurs de la plateforme directement depuis cette interface.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                <i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur
            </button>
        </div>
        
        <div class="content-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="users-table">
                    <thead>
                        <tr>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr id="user-row-<?= $user['id'] ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar"><?= strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) ?></div>
                                    <div class="ms-3">
                                        <div class="fw-bold user-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="user-email"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="user-role" data-role-id="<?= $user['role_id'] ?>"><?= htmlspecialchars($user['nom_role']) ?></td>
                            <td class="user-status"><?php
                                $status_class = $user['statut_compte'] == 'actif' ? 'text-bg-success' : 'text-bg-warning';
                                echo "<span class=\"badge $status_class\">" . ucfirst($user['statut_compte']) . "</span>";
                            ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary btn-edit" 
                                        data-user-id="<?= $user['id'] ?>"
                                        data-user-nom="<?= htmlspecialchars($user['nom']) ?>"
                                        data-user-prenom="<?= htmlspecialchars($user['prenom']) ?>"
                                        data-user-email="<?= htmlspecialchars($user['email']) ?>"
                                        data-user-role-id="<?= $user['role_id'] ?>"
                                        data-user-statut="<?= $user['statut_compte'] ?>"
                                        title="Modifier"><i class="fas fa-pencil-alt"></i></button>
                                <button class="btn btn-sm btn-outline-danger btn-delete" 
                                        data-user-id="<?= $user['id'] ?>" 
                                        data-user-name="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>" 
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

<!-- MODAL POUR AJOUTER/MODIFIER UN UTILISATEUR -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Ajouter un utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" name="user_id" id="user_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="role_id" class="form-label">Rôle</label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <option value="" disabled selected>Choisir un rôle...</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['nom_role']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                         <div class="col-md-6">
                            <label for="statut_compte" class="form-label">Statut</label>
                            <select class="form-select" id="statut_compte" name="statut_compte" required>
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="mot_de_passe" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe">
                            <small class="form-text text-muted" id="passwordHelp">Laissez vide pour ne pas changer le mot de passe existant.</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary" form="userForm" id="saveUserBtn">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/admin_users_list.js"></script>

</body>
</html>