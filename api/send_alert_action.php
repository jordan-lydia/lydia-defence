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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = $_POST['target'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $alert_type = $_POST['alert_type'] ?? 'info';

    $icons = [
        'info' => 'fas fa-info-circle',
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'danger' => 'fas fa-skull-crossbones'
    ];
    $icon = $icons[$alert_type] ?? 'fas fa-info-circle';

    if (empty($target) || empty($message)) {
        $response['message'] = 'Veuillez remplir tous les champs.';
        echo json_encode($response);
        exit();
    }

    try {
        $target_ids = [];
        if ($target === 'all') {
            $stmt = $pdo->query("SELECT id FROM utilisateurs");
            $target_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } elseif (strpos($target, 'role_') === 0) {
            $role_id = (int) str_replace('role_', '', $target);
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE role_id = ?");
            $stmt->execute([$role_id]);
            $target_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        if (empty($target_ids)) {
            $response['message'] = 'Aucun utilisateur trouvé pour cette cible.';
        } else {
            // Utilise notre fonction pour créer les notifications en masse
            create_notification($pdo, $target_ids, $message, $alert_type, $icon);
            $response = ['success' => true, 'message' => 'Alerte envoyée à ' . count($target_ids) . ' utilisateur(s).'];
        }

    } catch (PDOException $e) {
        $response['message'] = 'Erreur serveur lors de l\'envoi.';
        error_log("Erreur envoi alerte: " . $e->getMessage());
    }
}

echo json_encode($response);
?>