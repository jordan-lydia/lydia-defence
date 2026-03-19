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

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'save_user') {
        $user_id = $_POST['user_id'] ?? null;
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $role_id = $_POST['role_id'];
        $statut = $_POST['statut_compte'];
        $mot_de_passe = $_POST['mot_de_passe'];

        try {
            if (empty($user_id)) { // --- AJOUTER ---
                if (empty($mot_de_passe)) {
                    throw new Exception('Le mot de passe est obligatoire pour un nouvel utilisateur.');
                }
                $hashed_password = hash('sha256', $mot_de_passe);
                $sql = "INSERT INTO utilisateurs (nom, prenom, email, role_id, statut_compte, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([$nom, $prenom, $email, $role_id, $statut, $hashed_password]);
                $response = ['success' => true, 'message' => 'Utilisateur ajouté avec succès.'];
            } else { // --- MODIFIER ---
                if (!empty($mot_de_passe)) {
                    $hashed_password = hash('sha256', $mot_de_passe);
                    $sql = "UPDATE utilisateurs SET nom=?, prenom=?, email=?, role_id=?, statut_compte=?, mot_de_passe=? WHERE id=?";
                    $pdo->prepare($sql)->execute([$nom, $prenom, $email, $role_id, $statut, $hashed_password, $user_id]);
                } else {
                    $sql = "UPDATE utilisateurs SET nom=?, prenom=?, email=?, role_id=?, statut_compte=? WHERE id=?";
                    $pdo->prepare($sql)->execute([$nom, $prenom, $email, $role_id, $statut, $user_id]);
                }
                $response = ['success' => true, 'message' => 'Utilisateur modifié avec succès.'];
            }
        } catch (Exception $e) {
            $response['message'] = $e->getCode() == 23000 ? 'Cet email existe déjà.' : 'Erreur: ' . $e->getMessage();
        }
    }

    if ($action === 'delete_user') {
        $user_id = $_POST['user_id'] ?? null;
        if ($user_id && $user_id != $_SESSION['user_id']) { // Empêche l'admin de s'auto-supprimer
            try {
                $sql = "DELETE FROM utilisateurs WHERE id = ?";
                $pdo->prepare($sql)->execute([$user_id]);
                $response = ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
            } catch (Exception $e) {
                $response['message'] = 'Erreur lors de la suppression.';
            }
        } else {
            $response['message'] = 'Impossible de supprimer cet utilisateur.';
        }
    }
}
echo json_encode($response);
?>