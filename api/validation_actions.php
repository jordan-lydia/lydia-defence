<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'Action non autorisée.'];

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Gestionnaire', 'Administrateur'])) {
    echo json_encode($response);
    exit();
}

$gestionnaire_id = $_SESSION['user_id'];

if (isset($_POST['action']) && isset($_POST['declaration_id'])) {
    $action = $_POST['action'];
    $declaration_id = $_POST['declaration_id'];
    $commentaire = trim($_POST['commentaire'] ?? '');

    if ($action === 'approve') {
        $new_status = 'valide';
    } elseif ($action === 'reject') {
        if (empty($commentaire)) {
            $response['message'] = 'Un commentaire est obligatoire pour rejeter une déclaration.';
            echo json_encode($response);
            exit();
        }
        $new_status = 'rejete';
    } else {
        echo json_encode($response);
        exit();
    }

    try {
        $sql = "UPDATE declarations_deces SET statut_validation = ?, validateur_id = ?, date_validation = NOW(), commentaire_validation = ? WHERE id = ? AND statut_validation = 'en_attente'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $gestionnaire_id, $commentaire, $declaration_id]);
        
        if ($stmt->rowCount() > 0) {
            $action_text = ($new_status === 'valide') ? 'validé' : 'rejeté';
            log_action($pdo, "A $action_text la déclaration #$declaration_id.", 'INFO', $gestionnaire_id);
            $response = ['success' => true, 'message' => "La déclaration a été $action_text avec succès."];
        } else {
            $response['message'] = 'Cette déclaration a peut-être déjà été traitée.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Erreur serveur.';
    }
}
echo json_encode($response);
?>