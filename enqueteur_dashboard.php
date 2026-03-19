<?php
session_start();
require_once 'includes/db.php';

// Sécurité : vérifier si l'utilisateur est connecté et est un Enquêteur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Enquêteur') {
    header('Location: connexion.php');
    exit();
}

$enqueteur_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Traitement du formulaire de saisie
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et valider les données du formulaire
    $date_deces = $_POST['date_deces'];
    $sexe = $_POST['sexe'];
    $age_annees = filter_input(INPUT_POST, 'age_annees', FILTER_VALIDATE_INT);
    $zone_sante_id = filter_input(INPUT_POST, 'zone_sante_id', FILTER_VALIDATE_INT);
    $cause_deces_id = filter_input(INPUT_POST, 'cause_deces_id', FILTER_VALIDATE_INT);
    $cause_probable_texte = trim($_POST['cause_probable_texte']);

    // Validation simple
    if ($date_deces && $sexe && $age_annees !== false && $zone_sante_id && $cause_deces_id) {
        try {
            $sql = "INSERT INTO declarations_deces (date_deces, sexe, age_annees, zone_sante_id, cause_deces_id, cause_probable_texte, enqueteur_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date_deces, $sexe, $age_annees, $zone_sante_id, $cause_deces_id, $cause_probable_texte, $enqueteur_id]);
            $success_message = "Déclaration enregistrée avec succès !";
        } catch (PDOException $e) {
            $error_message = "Erreur lors de l'enregistrement. Veuillez réessayer.";
            error_log("Erreur de saisie: " . $e->getMessage());
        }
    } else {
        $error_message = "Veuillez remplir tous les champs obligatoires correctement.";
    }
}

try {
    // Récupérer les listes pour les menus déroulants du formulaire
    $zones_sante = $pdo->query("SELECT id, nom_zone FROM zones_sante ORDER BY nom_zone")->fetchAll();
    $causes_deces = $pdo->query("SELECT id, nom_cause FROM causes_deces ORDER BY nom_cause")->fetchAll();

    // Récupérer les KPIs pour l'enquêteur
    $stmt_today = $pdo->prepare("SELECT COUNT(id) FROM declarations_deces WHERE enqueteur_id = ? AND DATE(date_saisie) = CURDATE()");
    $stmt_today->execute([$enqueteur_id]);
    $saisies_aujourdhui = $stmt_today->fetchColumn();

    $stmt_month = $pdo->prepare("SELECT COUNT(id) FROM declarations_deces WHERE enqueteur_id = ? AND MONTH(date_saisie) = MONTH(CURDATE()) AND YEAR(date_saisie) = YEAR(CURDATE())");
    $stmt_month->execute([$enqueteur_id]);
    $saisies_ce_mois = $stmt_month->fetchColumn();

} catch (PDOException $e) {
    die("Erreur de chargement de la page: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Enquêteur</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css"> <!-- Réutilisation du CSS admin -->
</head>
<body class="admin-body">

<div class="admin-wrapper">
    
    <?php include 'includes/enqueteur_sidebar.php'; ?>

    <main class="content-wrapper">
        
        <?php include 'includes/enqueteur_navbar.php'; ?>
        
        <div class="page-header mb-4">
            <h1 class="page-title">Nouvelle Déclaration de Décès</h1>
            <p class="page-lead">Remplissez le formulaire ci-dessous pour enregistrer un nouveau cas.</p>
        </div>

        <!-- KPIs personnels de l'Enquêteur -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="card-icon bg-primary-light"><i class="fas fa-calendar-day"></i></div>
                    <div class="card-info"><h6>Saisies Aujourd'hui</h6><h5><?= $saisies_aujourdhui ?></h5></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="card-icon bg-success-light"><i class="fas fa-calendar-alt"></i></div>
                    <div class="card-info"><h6>Saisies ce mois-ci</h6><h5><?= $saisies_ce_mois ?></h5></div>
                </div>
            </div>
        </div>

        <!-- Formulaire de saisie -->
        <div class="content-block">
            <h5 class="content-block-header mb-4">Formulaire de Saisie</h5>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <form action="enqueteur_dashboard.php" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="date_deces" class="form-label">Date du décès *</label>
                        <input type="date" class="form-control" id="date_deces" name="date_deces" required>
                    </div>
                    <div class="col-md-6">
                        <label for="age_annees" class="form-label">Âge (en années) *</label>
                        <input type="number" class="form-control" id="age_annees" name="age_annees" required min="0" max="150">
                    </div>
                    <div class="col-md-6">
                        <label for="sexe" class="form-label">Sexe *</label>
                        <select class="form-select" id="sexe" name="sexe" required>
                            <option value="" selected disabled>Choisir...</option>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                            <option value="Inconnu">Inconnu</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="zone_sante_id" class="form-label">Zone de Santé *</label>
                        <select class="form-select" id="zone_sante_id" name="zone_sante_id" required>
                            <option value="" selected disabled>Choisir une zone...</option>
                            <?php foreach ($zones_sante as $zone): ?>
                                <option value="<?= $zone['id'] ?>"><?= htmlspecialchars($zone['nom_zone']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="cause_deces_id" class="form-label">Cause principale du décès *</label>
                        <select class="form-select" id="cause_deces_id" name="cause_deces_id" required>
                             <option value="" selected disabled>Choisir une cause...</option>
                            <?php foreach ($causes_deces as $cause): ?>
                                <option value="<?= $cause['id'] ?>"><?= htmlspecialchars($cause['nom_cause']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="cause_probable_texte" class="form-label">Détails supplémentaires (facultatif)</label>
                        <textarea class="form-control" id="cause_probable_texte" name="cause_probable_texte" rows="3" placeholder="Ex: circonstances de l'accident, symptômes observés..."></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Enregistrer la déclaration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>