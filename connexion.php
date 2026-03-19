<?php
// Démarrer la session en tout début de script.
session_start();

// Si l'utilisateur est déjà connecté, le rediriger vers son tableau de bord.
if (isset($_SESSION['user_id'])) {
    // Redirection simple pour l'exemple, à affiner si nécessaire
    header('Location: ' . strtolower($_SESSION['user_role']) . '_dashboard.php');
    exit();
}

$error_message = '';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Inclure le fichier de connexion à la base de données
    require_once 'includes/db.php';

    // Nettoyer les entrées
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        try {
            // Hasher le mot de passe fourni avec SHA256 pour le comparer
            // Note: En production moderne, il est fortement recommandé d'utiliser password_hash() et password_verify().
            $hashed_password = hash('sha256', $password);

            // Préparer la requête SQL pour éviter les injections
            // On récupère l'utilisateur ET son rôle en une seule requête grâce à une JOINTURE
            $sql = "SELECT utilisateurs.*, roles.nom_role 
                    FROM utilisateurs 
                    JOIN roles ON utilisateurs.role_id = roles.id 
                    WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Vérifier si un utilisateur a été trouvé ET si le mot de passe correspond
            if ($user && $hashed_password === $user['mot_de_passe']) {
                
                // Vérifier si le compte est actif
                if ($user['statut_compte'] !== 'actif') {
                    $error_message = "Votre compte est inactif ou suspendu. Veuillez contacter un administrateur.";
                } else {
                    // Le mot de passe est correct, la connexion est réussie !
                    // Enregistrer les informations de l'utilisateur dans la session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nom_complet'] = $user['prenom'] . ' ' . $user['nom'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['nom_role'];

                    // Mettre à jour la date de dernière connexion
                    $update_sql = "UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?";
                    $pdo->prepare($update_sql)->execute([$user['id']]);

                    // --- LOGIQUE DE REDIRECTION BASÉE SUR LE RÔLE ---
                    // C'est ici que la magie opère.
                    switch ($user['nom_role']) {
                        case 'Administrateur':
                            header('Location: admin_dashboard.php');
                            break;
                        case 'Gestionnaire':
                            header('Location: gestionnaire_dashboard.php');
                            break;
                        case 'Enquêteur':
                            header('Location: enqueteur_dashboard.php');
                            break;
                        case 'Décideur':
                            header('Location: decideur_dashboard.php');
                            break;
                        default:
                            // Redirection par défaut si le rôle n'a pas de tableau de bord défini
                            header('Location: index.php');
                            break;
                    }
                    exit(); // Très important d'arrêter le script après une redirection
                }
            } else {
                // Email ou mot de passe incorrect
                $error_message = "L'adresse email ou le mot de passe est incorrect.";
            }

        } catch (PDOException $e) {
            $error_message = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
            error_log("Erreur de connexion: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - STDM</title>

    <!-- Inclusion des mêmes styles que l'accueil pour une expérience cohérente -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    
    <style>
        /* Styles spécifiques pour la page de connexion */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.5rem;
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            color: var(--light-text);
        }
        .login-card-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-card-header .icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .login-card-header h2 {
            font-weight: 600;
        }
        .form-control {
            height: 50px;
        }
        .btn-submit-login {
            background: linear-gradient(45deg, var(--primary-color), #00c4b7);
            border: none;
            padding: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 167, 157, 0.3);
        }
    </style>
</head>
<body>

    <div id="particles-js"></div>

    <div class="container login-container">
        <div class="login-card" data-aos="zoom-in">
            <div class="login-card-header">
                <div class="icon"><i class="fas fa-user-shield"></i></div>
                <h2>Accès Sécurisé</h2>
                <p class="text-white-50">Plateforme de Suivi des Taux de Mortalité</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form action="connexion.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="votre.email@sante.cd" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-submit-login">Se connecter</button>
                </div>
                <div class="text-center mt-4">
                    <a href="index.php" class="text-white-50" style="text-decoration: none;"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts JS (identiques à l'accueil) -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/script.js"></script>
</body>
</html>