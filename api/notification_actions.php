<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

$response = ['success' => false, 'message' => 'Action non autorisée.'];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'mark_as_read' && isset($_POST['notif_id'])) {
            $notif_id = $_POST['notif_id'];
            $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
            $pdo->prepare($sql)->execute([$notif_id, $user_id]);
            $response = ['success' => true];
        }

        if ($action === 'mark_all_as_read') {
            $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
            $pdo->prepare($sql)->execute([$user_id]);
            $response = ['success' => true];
        }
    } catch (PDOException $e) {
        $response['message'] = 'Erreur serveur.';
    }
}
echo json_encode($response);
?>