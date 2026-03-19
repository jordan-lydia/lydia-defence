<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

$response = ['success' => false, 'message' => 'Action non autorisée.'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    echo json_encode($response);
    exit();
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'save_cause') {
        $cause_id = $_POST['cause_id'] ?? null;
        $nom_cause = trim($_POST['nom_cause']);
        $categorie = trim($_POST['categorie']);
        $code_cim10 = trim($_POST['code_cim10']);

        if (empty($nom_cause)) {
            $response['message'] = 'Le nom de la cause est obligatoire.';
            echo json_encode($response);
            exit();
        }

        try {
            if (empty($cause_id)) {
                $sql = "INSERT INTO causes_deces (nom_cause, categorie, code_cim10) VALUES (?, ?, ?)";
                $pdo->prepare($sql)->execute([$nom_cause, $categorie, $code_cim10]);
                $response = ['success' => true, 'message' => "La cause '$nom_cause' a été ajoutée."];
            } else {
                $sql = "UPDATE causes_deces SET nom_cause = ?, categorie = ?, code_cim10 = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$nom_cause, $categorie, $code_cim10, $cause_id]);
                $response = ['success' => true, 'message' => "La cause '$nom_cause' a été modifiée."];
            }
        } catch (PDOException $e) {
            $response['message'] = 'Erreur serveur: ' . $e->getMessage();
        }
    }

    if ($action === 'delete_cause') {
        $cause_id = $_POST['cause_id'] ?? null;
        if ($cause_id) {
            try {
                $sql = "DELETE FROM causes_deces WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$cause_id]);

                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'Cause supprimée avec succès.'];
                } else {
                    $response['message'] = 'Cause non trouvée.';
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $response['message'] = 'Impossible de supprimer cette cause car elle est utilisée dans des déclarations existantes.';
                } else {
                    $response['message'] = 'Erreur lors de la suppression.';
                }
            }
        } else {
            $response['message'] = 'ID de cause manquant.';
        }
    }
}
echo json_encode($response);
?>