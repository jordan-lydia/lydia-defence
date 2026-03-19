<?php
// Fichier: logout.php

// 1. Démarrer la session
// Il est essentiel de démarrer la session pour pouvoir y accéder et la détruire.
session_start();

// 2. Détruire toutes les variables de session
// unset($_SESSION) viderait le tableau, mais la méthode ci-dessous est plus complète.
$_SESSION = array();

// 3. Détruire le cookie de session (si utilisé)
// Cette étape est importante pour une déconnexion complète.
// Si vous voulez détruire la session, vous devez aussi détruire le cookie de session.
// Note : Cela détruira la session et pas seulement les données de session !
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalement, détruire la session elle-même.
session_destroy();

// 5. Rediriger l'utilisateur vers la page de connexion
// Après la déconnexion, l'utilisateur est renvoyé à la page de connexion
// pour pouvoir s'authentifier à nouveau s'il le souhaite.
header("Location: connexion.php");

// 6. S'assurer que le script s'arrête après la redirection
// C'est une bonne pratique de sécurité pour éviter toute exécution de code
// non désirée après l'envoi de l'en-tête de redirection.
exit();
?>