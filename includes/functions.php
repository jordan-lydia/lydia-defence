<?php
// Fichier: includes/functions.php

/**
 * Crée une notification pour un ou plusieurs utilisateurs.
 *
 * @param PDO $pdo L'objet de connexion PDO.
 * @param int|array $userId L'ID de l'utilisateur ou un tableau d'IDs.
 * @param string $message Le message de la notification.
 * @param string $type Le type ('info', 'success', 'warning', 'danger').
 * @param string $icon L'icône FontAwesome (ex: 'fas fa-user-plus').
 * @param string|null $link Le lien associé à la notification.
 */
function create_notification($pdo, $userId, $message, $type = 'info', $icon = 'fas fa-info-circle', $link = null) {
    $sql = "INSERT INTO notifications (user_id, type, icon, message, link) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if (is_array($userId)) {
        foreach ($userId as $id) {
            $stmt->execute([$id, $type, $icon, $message, $link]);
        }
    } else {
        $stmt->execute([$userId, $type, $icon, $message, $link]);
    }
}
/**
 * Enregistre une action dans le journal du système.
 *
 * @param PDO $pdo L'objet de connexion PDO.
 * @param string $action La description de l'action.
 * @param string $level Le niveau de criticité ('INFO', 'WARNING', 'DANGER', 'AUTH').
 * @param int|null $userId L'ID de l'utilisateur qui effectue l'action.
 */
function log_action($pdo, $action, $level = 'INFO', $userId = null) {
    $sql = "INSERT INTO system_logs (user_id, level, action, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    
    // Si l'ID n'est pas fourni, on essaie de le prendre de la session
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    $stmt->execute([$userId, $level, $action, $ip_address, $user_agent]);
}

?>