<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STDM - Suivi des Taux de Mortalité | Lubumbashi</title>

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6.4.2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- AOS (Animate On Scroll) CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Votre Feuille de Style Personnalisée -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <!-- Conteneur pour l'animation de particules en fond -->
    <div id="particles-js"></div>

    <!-- Barre de Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-line"></i> STDM
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Fonctionnalités</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stats">Impact</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">Comment ça marche ?</a></li>
                    <li class="nav-item">
                    <a href="connexion.php" class="btn btn-outline-light btn-login">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a>
                </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section Héros -->
    <header class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title" data-aos="fade-down">Transformer les Données en Actions de Santé Publique</h1>
            <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="200">
                La plateforme centralisée pour la collecte, l'analyse et la visualisation des statistiques de mortalité à Lubumbashi.
            </p>
            <a href="#features" class="btn btn-primary btn-lg mt-4" data-aos="zoom-in" data-aos-delay="400">
                Découvrir la plateforme <i class="fas fa-arrow-down"></i>
            </a>
        </div>
    </header>

    <main>
        <!-- Section Fonctionnalités -->
        <section id="features" class="py-5">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title">Une Plateforme Puissante et Intuitive</h2>
                    <p class="section-lead">Conçue pour les professionnels de la santé sur le terrain et les décideurs.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-delay="100">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-database"></i></div>
                            <h3>Collecte Fiable</h3>
                            <p>Saisissez les données de manière structurée et sécurisée, directement depuis le terrain, pour une information de première main.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                            <h3>Analyse en Temps Réel</h3>
                            <p>Accédez à des tableaux de bord dynamiques qui traduisent les chiffres bruts en tendances compréhensibles instantanément.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6" data-aos="fade-left" data-aos-delay="300">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-map-marked-alt"></i></div>
                            <h3>Visualisation Géospatiale</h3>
                            <p>Identifiez les zones à risque et les "points chauds" grâce à des cartes interactives pour des interventions ciblées et efficaces.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section Statistiques d'Impact -->
        <section id="stats" class="py-5">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-4" data-aos="zoom-in">
                        <div class="stat-item">
                            <i class="fas fa-heartbeat"></i>
                            <h3 class="counter" data-count="15000">0</h3>
                            <p>Données de Mortalité Traitées</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                        <div class="stat-item">
                            <i class="fas fa-clinic-medical"></i>
                            <h3 class="counter" data-count="45">0</h3>
                            <p>Zones de Santé Couvertes</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
                        <div class="stat-item">
                            <i class="fas fa-lightbulb"></i>
                            <h3 class="counter" data-count="95">0</h3>
                            <p>% de Décisions Mieux Éclairées</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Section Comment ça marche -->
        <section id="how-it-works" class="py-5">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title">Un Processus Simplifié en 3 Étapes</h2>
                </div>
                <div class="row">
                    <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="100">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <i class="fas fa-keyboard step-icon"></i>
                            <h4>Saisir</h4>
                            <p>L'agent sur le terrain enregistre les données via un formulaire simple et standardisé.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="300">
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <i class="fas fa-cogs step-icon"></i>
                            <h4>Analyser</h4>
                            <p>La plateforme centralise et traite les données pour générer des analyses pertinentes.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center" data-aos="fade-up" data-aos-delay="500">
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <i class="fas fa-bullseye step-icon"></i>
                            <h4>Agir</h4>
                            <p>Les décideurs utilisent les visualisations pour mettre en place des actions ciblées.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Pied de Page -->
    <footer class="text-center py-4">
        <div class="container">
            <p>© 2023 STDM Lubumbashi. Tous droits réservés. Une initiative pour la santé publique.</p>
        </div>
    </footer>


    <!-- Modal de Connexion -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Connexion à la Plateforme</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Le formulaire sera traité par un fichier PHP (ex: process_login.php) -->
                    <form action="process_login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="votre.email@sante.cd" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                             <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="#" class="text-muted">Mot de passe oublié ?</a>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts JS -->
    <!-- Bootstrap 5.3.2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

    <!-- AOS (Animate On Scroll) JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Votre Script Personnalisé -->
    <script src="js/script.js"></script>

</body>
</html>
