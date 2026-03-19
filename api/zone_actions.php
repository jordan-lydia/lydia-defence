<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'Action non autorisée.'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    echo json_encode($response);
    exit();
}

$admin_id = $_SESSION['user_id'];

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'save_zone') {
        $zone_id = $_POST['zone_id'] ?? null;
        $nom_zone = trim($_POST['nom_zone']);
        $commune = trim($_POST['commune']);

        if (empty($nom_zone)) {
            $response['message'] = 'Le nom de la zone est obligatoire.';
            echo json_encode($response);
            exit();
        }

        try {
            if (empty($zone_id)) {
                $sql = "INSERT INTO zones_sante (nom_zone, commune) VALUES (?, ?)";
                $pdo->prepare($sql)->execute([$nom_zone, $commune]);
                $message = "La zone '$nom_zone' a été ajoutée.";
                $response = ['success' => true, 'message' => $message];
                create_notification($pdo, $admin_id, $message, 'success', 'fas fa-map-marker-alt');
            } else {
                $sql = "UPDATE zones_sante SET nom_zone = ?, commune = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$nom_zone, $commune, $zone_id]);
                $message = "La zone '$nom_zone' a été modifiée.";
                $response = ['success' => true, 'message' => $message];
            }
        } catch (PDOException $e) {
            $response['message'] = $e->getCode() == 23000 ? 'Ce nom de zone existe déjà.' : 'Erreur serveur: ' . $e->getMessage();
        }
    }

    if ($action === 'delete_zone') {
        $zone_id = $_POST['zone_id'] ?? null;
        if ($zone_id) {
            try {
                $sql = "DELETE FROM zones_sante WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$zone_id]);

                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'Zone supprimée avec succès.'];
                } else {
                    $response['message'] = 'Zone non trouvée.';
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $response['message'] = 'Impossible de supprimer cette zone car elle est utilisée dans des déclarations existantes.';
                } else {
                    $response['message'] = 'Erreur lors de la suppression.';
                }
            }
        } else {
            $response['message'] = 'ID de zone manquant.';
        }
    }
}
echo json_encode($response);
?>