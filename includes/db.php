<?php
// Fichier : includes/db.php

// --- Configuration de la base de données ---
// Assure-toi que ces informations correspondent à ta configuration locale.
define('DB_HOST', 'localhost');
define('DB_NAME', 'taux_de_mortalite'); // Le nom de ta base de données
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- Création du DSN (Data Source Name) ---
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// --- Options de PDO pour une connexion robuste et sécurisée ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,      // Lève des exceptions en cas d'erreur SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Récupère les résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                     // Utilise les vraies requêtes préparées pour plus de sécurité
];

// --- Tentative de connexion ---
try {
    // Crée une nouvelle instance de l'objet PDO. C'est notre objet de connexion.
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // En cas d'échec de la connexion, on arrête tout et on affiche un message d'erreur clair.
    // Pour une application en production, il faudrait logger cette erreur dans un fichier
    // et afficher un message plus générique à l'utilisateur.
    // Pour le développement, afficher l'erreur est très utile pour le débogage.
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("ERREUR: Impossible de se connecter à la base de données. Veuillez contacter un administrateur.");
}
?>