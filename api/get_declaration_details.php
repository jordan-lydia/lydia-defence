<?php
session_start();
require_once '../includes/db.php';

// --- CORRECTION DE LA PERMISSION ---
// On autorise maintenant les Administrateurs, les Gestionnaires ET les Enquêteurs
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Administrateur', 'Gestionnaire', 'Enquêteur'])) {
    http_response_code(403);
    echo '<div class="modal-header"><h5 class="modal-title text-danger">Accès Refusé</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Vous n\'avez pas les permissions nécessaires pour voir ces détails.</p></div>';
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo 'ID de déclaration manquant.';
    exit();
}

$declaration_id = $_GET['id'];

try {
    $sql = "
        SELECT 
            dd.*, zs.nom_zone, cd.nom_cause,
            enqueteur.prenom as enqueteur_prenom, enqueteur.nom as enqueteur_nom,
            validateur.prenom as validateur_prenom, validateur.nom as validateur_nom
        FROM declarations_deces dd
        JOIN zones_sante zs ON dd.zone_sante_id = zs.id
        JOIN causes_deces cd ON dd.cause_deces_id = cd.id
        JOIN utilisateurs enqueteur ON dd.enqueteur_id = enqueteur.id
        LEFT JOIN utilisateurs validateur ON dd.validateur_id = validateur.id
        WHERE dd.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$declaration_id]);
    $dec = $stmt->fetch();

    if (!$dec) {
        http_response_code(404);
        echo 'Déclaration non trouvée.';
        exit();
    }
    
    // Sécurité supplémentaire : un enquêteur ne peut voir que ses propres saisies.
    if ($user_role === 'Enquêteur' && $dec['enqueteur_id'] != $user_id) {
        http_response_code(403);
        echo '<div class="modal-header"><h5 class="modal-title text-danger">Accès Refusé</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Vous ne pouvez consulter que vos propres déclarations.</p></div>';
        exit();
    }

    // --- Construction de la réponse HTML ---
    ?>
    <div class="modal-header">
        <h5 class="modal-title">Détails de la Déclaration #<?= $dec['id'] ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Informations sur le cas</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><strong>Date du décès:</strong> <span><?= htmlspecialchars(date('d/m/Y', strtotime($dec['date_deces']))) ?></span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Sexe:</strong> <span><?= $dec['sexe'] == 'M' ? 'Masculin' : ($dec['sexe'] == 'F' ? 'Féminin' : 'Inconnu') ?></span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Âge:</strong> <span><?= $dec['age_annees'] ?> ans</span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Zone de Santé:</strong> <span><?= htmlspecialchars($dec['nom_zone']) ?></span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Cause principale:</strong> <span><?= htmlspecialchars($dec['nom_cause']) ?></span></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Informations de suivi</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><strong>Statut:</strong> <span>
                        <?php 
                            $status = $dec['statut_validation'];
                            $badge_class = 'text-bg-secondary';
                            if ($status == 'en_attente') $badge_class = 'text-bg-warning';
                            elseif ($status == 'valide') $badge_class = 'text-bg-success';
                            elseif ($status == 'rejete') $badge_class = 'text-bg-danger';
                            echo "<span class=\"badge $badge_class\">" . ucfirst($status) . "</span>";
                        ?>
                    </span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Saisi par:</strong> <span><?= htmlspecialchars($dec['enqueteur_prenom'] . ' ' . $dec['enqueteur_nom']) ?></span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Date de saisie:</strong> <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($dec['date_saisie']))) ?></span></li>
                    <?php if ($dec['statut_validation'] != 'en_attente'): ?>
                        <li class="list-group-item d-flex justify-content-between"><strong>Traité par:</strong> <span><?= htmlspecialchars($dec['validateur_prenom'] . ' ' . $dec['validateur_nom']) ?></span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Date de traitement:</strong> <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($dec['date_validation']))) ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php if (!empty($dec['cause_probable_texte']) || !empty($dec['commentaire_validation'])): ?>
            <div class="col-12 mt-3">
                <h6>Notes & Commentaires</h6>
                <?php if (!empty($dec['cause_probable_texte'])): ?>
                    <p class="notes-section"><strong>Détails de l'enquêteur:</strong><br><?= nl2br(htmlspecialchars($dec['cause_probable_texte'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($dec['commentaire_validation'])): ?>
                    <p class="notes-section"><strong>Commentaire du gestionnaire:</strong><br><?= nl2br(htmlspecialchars($dec['commentaire_validation'])) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="modal-footer">
        <?php if ($user_role === 'Gestionnaire' && $dec['statut_validation'] === 'en_attente'): ?>
            <button type="button" class="btn btn-lg btn-success" id="openActionModalBtn"><i class="fas fa-gavel me-2"></i>Prendre une Décision</button>
        <?php endif; ?>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
    </div>
    <?php
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erreur du serveur.';
}
?>