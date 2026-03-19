<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header('Location: connexion.php');
    exit();
}

function time_elapsed_string($datetime, $full = false) {
    try {
        $now = new DateTime;
        $ago = new DateTime($datetime);
    } catch (Exception $e) {
        return 'Date invalide';
    }
    
    $diff = $now->diff($ago);

    $w = floor($diff->d / 7);
    $d = $diff->d - ($w * 7);

    $string = [
        'y' => $diff->y, 'm' => $diff->m, 'w' => $w, 'd' => $d,
        'h' => $diff->h, 'i' => $diff->i, 's' => $diff->s
    ];
    $string_labels = [
        'y' => 'an', 'm' => 'mois', 'w' => 'semaine', 'd' => 'jour',
        'h' => 'heure', 'i' => 'minute', 's' => 'seconde'
    ];
    
    $result = [];
    foreach ($string as $key => $value) {
        if ($value > 0) {
            $label = $string_labels[$key];
            $result[] = $value . ' ' . $label . ($value > 1 ? 's' : '');
        }
    }

    if (!$full) {
        $result = array_slice($result, 0, 1);
    }
    
    return !empty($result) ? 'Il y a ' . implode(', ', $result) : 'À l\'instant';
}

try {
    $sql = "
        SELECT 
            dd.id, dd.date_deces, dd.date_saisie,
            zs.nom_zone, 
            cd.nom_cause,
            enqueteur.prenom as enqueteur_prenom, enqueteur.nom as enqueteur_nom
        FROM declarations_deces dd
        JOIN zones_sante zs ON dd.zone_sante_id = zs.id
        JOIN causes_deces cd ON dd.cause_deces_id = cd.id
        JOIN utilisateurs enqueteur ON dd.enqueteur_id = enqueteur.id
        WHERE dd.statut_validation = 'en_attente'
        ORDER BY dd.date_saisie ASC";
    $stmt = $pdo->query($sql);
    $declarations_pending = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déclarations en Attente - Admin STDM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_declarations_pending.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="content-wrapper">
        <?php include 'includes/admin_navbar.php'; ?>
        
        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Déclarations en Attente de Validation</h1>
                <p class="page-lead">Liste des déclarations soumises et non encore traitées.</p>
            </div>
        </div>
        
        <div class="content-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date Saisie</th>
                            <th>En Attente Depuis</th>
                            <th>Zone</th>
                            <th>Cause</th>
                            <th>Enquêteur</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($declarations_pending)): ?>
                            <tr>
                                <td colspan="6" class="text-center p-5">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h5 class="text-muted">Aucune déclaration en attente.</h5>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($declarations_pending as $dec): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($dec['date_saisie']))) ?></td>
                                <td class="time-since"><?= time_elapsed_string($dec['date_saisie']) ?></td>
                                <td><?= htmlspecialchars($dec['nom_zone']) ?></td>
                                <td><?= htmlspecialchars($dec['nom_cause']) ?></td>
                                <td><?= htmlspecialchars($dec['enqueteur_prenom'] . ' ' . $dec['enqueteur_nom']) ?></td>
                                <td class="text-end">
                                    <a href="admin_declarations_list.php" class="btn btn-sm btn-outline-primary" title="Voir dans la liste complète"><i class="fas fa-eye"></i></a>
                                    <button class="btn btn-sm btn-outline-warning" title="Notifier un gestionnaire"><i class="fas fa-bell"></i></button>
                                </td>
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
<script src="js/admin_declarations_pending.js"></script>
</body>
</html>