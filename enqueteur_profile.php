<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Enquêteur') {
    header('Location: connexion.php');
    exit();
}

$enqueteur_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Traitement du formulaire de changement de mot de passe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
            $stmt->execute([$enqueteur_id]);
            $user = $stmt->fetch();
            
            $hashed_current_password = hash('sha256', $current_password);

            if ($user && $hashed_current_password === $user['mot_de_passe']) {
                $hashed_new_password = hash('sha256', $new_password);
                $update_stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                $update_stmt->execute([$hashed_new_password, $enqueteur_id]);

                log_action($pdo, 'Changement de son propre mot de passe.', 'AUTH', $enqueteur_id);
                
                $success_message = "Votre mot de passe a été modifié avec succès.";
            } else {
                $error_message = "Le mot de passe actuel est incorrect.";
            }

        } catch (PDOException $e) {
            $error_message = "Une erreur serveur est survenue.";
            error_log("Erreur chgmt mdp: " . $e->getMessage());
        }
    }
}

// Récupérer les informations de l'enquêteur pour affichage
try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$enqueteur_id]);
    $user_data = $stmt->fetch();
} catch (PDOException $e) {
    die("Erreur de récupération des informations du profil.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Enquêteur</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/dash.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/enqueteur_sidebar.php'; ?>
    <div class="main-panel">
        <?php include 'includes/enqueteur_navbar.php'; ?>
        <main class="content-wrapper">
        
            <div class="page-header mb-4">
                <h1 class="page-title">Mon Profil</h1>
                <p class="page-lead">Gérez vos informations personnelles et votre mot de passe.</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="content-block text-center p-4 h-100">
                        <img src="images/profile-placeholder.png" alt="Profile" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px;">
                        <h4 class="mb-1"><?= htmlspecialchars($user_data['prenom'] . ' ' . $user_data['nom']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($user_data['email']) ?></p>
                        <span class="badge text-bg-secondary">Enquêteur</span>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="content-block h-100">
                        <h5 class="content-block-header mb-4">Changer mon mot de passe</h5>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?= $success_message ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?= $error_message ?></div>
                        <?php endif; ?>

                        <form action="enqueteur_profile.php" method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Mettre à jour
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>