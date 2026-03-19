-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 30 juin 2025 à 20:40
-- Version du serveur : 5.7.36
-- Version de PHP : 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `taux_de_mortalite`
--

-- --------------------------------------------------------

--
-- Structure de la table `causes_deces`
--

DROP TABLE IF EXISTS `causes_deces`;
CREATE TABLE IF NOT EXISTS `causes_deces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_cim10` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_cause` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Non classée',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_cim10` (`code_cim10`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `causes_deces`
--

INSERT INTO `causes_deces` (`id`, `code_cim10`, `nom_cause`, `categorie`) VALUES
(1, 'B50-B54', 'Paludisme', 'Maladies infectieuses'),
(2, 'A15-A19', 'Tuberculose', 'Maladies infectieuses'),
(3, 'J09-J18', 'Infections respiratoires aiguës', 'Maladies respiratoires'),
(4, 'A00-A09', 'Maladies diarrhéiques', 'Maladies infectieuses'),
(5, 'P00-P96', 'Affections périnatales', 'Mortalité néonatale'),
(6, 'I20-I25', 'Cardiopathies ischémiques', 'Maladies non transmissibles'),
(7, 'V01-Y98', 'Causes externes (accidents, violences)', 'Traumatismes'),
(8, 'R99', 'Cause inconnue ou non spécifiée', 'Inclassable');

-- --------------------------------------------------------

--
-- Structure de la table `declarations_deces`
--

DROP TABLE IF EXISTS `declarations_deces`;
CREATE TABLE IF NOT EXISTS `declarations_deces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_deces` date NOT NULL,
  `sexe` enum('M','F','Inconnu') COLLATE utf8mb4_unicode_ci NOT NULL,
  `age_annees` int(3) NOT NULL,
  `age_mois` int(2) DEFAULT '0',
  `zone_sante_id` int(11) NOT NULL,
  `cause_deces_id` int(11) NOT NULL,
  `cause_probable_texte` text COLLATE utf8mb4_unicode_ci,
  `enqueteur_id` int(11) NOT NULL,
  `date_saisie` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statut_validation` enum('en_attente','valide','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `validateur_id` int(11) DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `commentaire_validation` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_declarations_zone_id` (`zone_sante_id`),
  KEY `fk_declarations_cause_id` (`cause_deces_id`),
  KEY `fk_declarations_enqueteur_id` (`enqueteur_id`),
  KEY `fk_declarations_validateur_id` (`validateur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `declarations_deces`
--

INSERT INTO `declarations_deces` (`id`, `date_deces`, `sexe`, `age_annees`, `age_mois`, `zone_sante_id`, `cause_deces_id`, `cause_probable_texte`, `enqueteur_id`, `date_saisie`, `statut_validation`, `validateur_id`, `date_validation`, `commentaire_validation`) VALUES
(1, '2023-10-15', 'M', 45, 0, 1, 1, NULL, 3, '2025-06-30 09:22:44', 'valide', 2, '2023-10-16 10:00:00', 'Données conformes.'),
(2, '2023-10-14', 'F', 0, 8, 2, 5, NULL, 4, '2025-06-30 09:22:44', 'valide', 2, '2023-10-16 10:05:00', 'Validé.'),
(3, '2023-10-16', 'M', 68, 0, 3, 7, NULL, 3, '2025-06-30 09:22:44', 'rejete', 2, '2023-10-17 11:30:00', 'Manque d\'informations sur les circonstances de l\'accident.'),
(4, '2023-10-18', 'F', 32, 0, 4, 2, NULL, 4, '2025-06-30 09:22:44', 'valide', 2, '2025-06-30 17:14:10', ''),
(5, '2023-10-19', 'M', 2, 0, 1, 4, NULL, 3, '2025-06-30 09:22:44', 'rejete', 2, '2025-06-30 17:17:02', 'je rejette c\'est tout'),
(6, '2025-06-30', 'M', 25, 0, 7, 5, 'maladie de long date', 3, '2025-06-30 16:08:52', 'valide', 2, '2025-06-30 18:09:37', ''),
(7, '2025-06-30', 'F', 23, 0, 5, 1, '', 3, '2025-06-30 17:17:57', 'en_attente', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('info','success','warning','danger') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fas fa-info-circle',
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notifications_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `icon`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 1, 'success', 'fas fa-user-plus', 'L\'utilisateur P. Kasongo a été créé.', 'admin_users_list.php', 0, '2025-06-30 10:43:14'),
(2, 1, 'warning', 'fas fa-file-medical-alt', 'Une nouvelle déclaration attend votre validation.', 'admin_declarations_pending.php', 0, '2025-06-30 10:43:14'),
(3, 1, 'success', 'fas fa-map-marker-alt', 'La zone \'dxgbhj\' a été ajoutée.', NULL, 0, '2025-06-30 11:47:58'),
(4, 1, 'info', 'fas fa-info-circle', 'dd', NULL, 1, '2025-06-30 14:37:04'),
(5, 2, 'info', 'fas fa-info-circle', 'dd', NULL, 0, '2025-06-30 14:37:04'),
(6, 3, 'info', 'fas fa-info-circle', 'dd', NULL, 0, '2025-06-30 14:37:04'),
(7, 4, 'info', 'fas fa-info-circle', 'dd', NULL, 0, '2025-06-30 14:37:04'),
(8, 7, 'info', 'fas fa-info-circle', 'dd', NULL, 0, '2025-06-30 14:37:04');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_role` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_role` (`nom_role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `nom_role`, `description_role`) VALUES
(1, 'Administrateur', 'Accès total à toutes les fonctionnalités, y compris la gestion des utilisateurs et la configuration du système.'),
(2, 'Gestionnaire', 'Peut analyser les données, générer les rapports, et valider ou rejeter les déclarations des enquêteurs.'),
(3, 'Enquêteur', 'Rôle de terrain. Peut uniquement saisir les données de mortalité et consulter l\'historique de ses propres saisies.'),
(4, 'Décideur', 'Accès en lecture seule aux tableaux de bord et aux rapports finaux pour la prise de décision stratégique.');

-- --------------------------------------------------------

--
-- Structure de la table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `level` enum('INFO','WARNING','DANGER','AUTH') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INFO',
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_logs_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `level`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 'INFO', 'A validé la déclaration #4.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-30 15:14:10'),
(2, 2, 'INFO', 'A rejeté la déclaration #5.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-30 15:17:02'),
(3, 2, 'INFO', 'A validé la déclaration #6.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-30 16:09:37');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int(11) NOT NULL,
  `statut_compte` enum('actif','inactif','suspendu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'actif',
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_utilisateurs_role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role_id`, `statut_compte`, `date_creation`, `derniere_connexion`) VALUES
(1, 'Admin', 'Principal', 'admin@sante.cd', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 1, 'actif', '2025-06-30 09:22:44', '2025-06-30 15:55:40'),
(2, 'Mulumba', 'Jean', 'gestionnaire.mulumba@sante.cd', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 2, 'actif', '2025-06-30 09:22:44', '2025-06-30 17:13:08'),
(3, 'Kabeya', 'Marie', 'enqueteur.kabeya@sante.cd', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 3, 'actif', '2025-06-30 09:22:44', '2025-06-30 19:04:16'),
(4, 'Kasongo', 'Pierre', 'enqueteur.kasongo@sante.cd', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 3, 'actif', '2025-06-30 09:22:44', '2025-06-30 12:32:47'),
(7, 'Kamba Mulamba', 'Dr. Samuel Roger', 'samuelroger@gmail.com', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 4, 'actif', '2025-06-30 11:08:24', '2025-06-30 19:19:22');

-- --------------------------------------------------------

--
-- Structure de la table `zones_sante`
--

DROP TABLE IF EXISTS `zones_sante`;
CREATE TABLE IF NOT EXISTS `zones_sante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_zone` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commune` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Haut-Katanga',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_zone` (`nom_zone`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `zones_sante`
--

INSERT INTO `zones_sante` (`id`, `nom_zone`, `commune`, `province`, `latitude`, `longitude`) VALUES
(1, 'Kamalondo', 'Kamalondo', 'Haut-Katanga', '-11.67910000', '27.48350000'),
(2, 'Kenya', 'Kenya', 'Haut-Katanga', '-11.66690000', '27.47160000'),
(3, 'Ruashi', 'Ruashi', 'Haut-Katanga', '-11.64450000', '27.50240000'),
(4, 'Katuba', 'Katuba', 'Haut-Katanga', '-11.68520000', '27.45210000'),
(5, 'Lubumbashi', 'Lubumbashi', 'Haut-Katanga', '-11.66030000', '27.47820000'),
(6, 'Kampemba', 'Kampemba', 'Haut-Katanga', '-11.65770000', '27.51860000'),
(7, 'Annexe', 'Annexe', 'Haut-Katanga', '-11.77610000', '27.47350000');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `declarations_deces`
--
ALTER TABLE `declarations_deces`
  ADD CONSTRAINT `fk_declarations_cause_id` FOREIGN KEY (`cause_deces_id`) REFERENCES `causes_deces` (`id`),
  ADD CONSTRAINT `fk_declarations_enqueteur_id` FOREIGN KEY (`enqueteur_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `fk_declarations_validateur_id` FOREIGN KEY (`validateur_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `fk_declarations_zone_id` FOREIGN KEY (`zone_sante_id`) REFERENCES `zones_sante` (`id`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `fk_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_utilisateurs_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
