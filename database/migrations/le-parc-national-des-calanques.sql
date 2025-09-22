-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3308
-- Généré le : lun. 15 sep. 2025 à 10:51
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `le-parc-national-des-calanques`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnement_utilisateur`
--

DROP TABLE IF EXISTS `abonnement_utilisateur`;
CREATE TABLE IF NOT EXISTS `abonnement_utilisateur` (
  `id_abonnement_utilisateur` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `id_type_abonnement` int NOT NULL,
  `id_paiement` int DEFAULT NULL COMMENT 'Référence vers le paiement',
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `statut` enum('actif','expire','suspendu','annule') DEFAULT 'actif',
  `auto_renouvellement` tinyint(1) DEFAULT '0',
  `reduction_appliquee` decimal(5,2) DEFAULT '0.00' COMMENT 'Réduction spéciale appliquée (%)',
  `code_promo` varchar(50) DEFAULT NULL COMMENT 'Code promo utilisé',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_abonnement_utilisateur`),
  KEY `idx_utilisateur` (`id_utilisateur`),
  KEY `idx_type_abonnement` (`id_type_abonnement`),
  KEY `idx_statut` (`statut`),
  KEY `idx_dates` (`date_debut`,`date_fin`),
  KEY `idx_paiement` (`id_paiement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `camping`
--

DROP TABLE IF EXISTS `camping`;
CREATE TABLE IF NOT EXISTS `camping` (
  `id_camping` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `capacite` int DEFAULT '0',
  `localisation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_camping`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `camping`
--

INSERT INTO `camping` (`id_camping`, `nom`, `capacite`, `localisation`, `created_at`, `updated_at`) VALUES
(1, 'Camping Sormiou', 50, 'Calanque de Sormiou', '2025-09-11 09:34:19', '2025-09-11 09:34:19'),
(2, 'Camping Morgiou', 35, 'Calanque de Morgiou', '2025-09-11 09:34:19', '2025-09-11 09:34:19'),
(3, 'Camping En-Vau', 25, 'Calanque d En-Vau', '2025-09-11 09:34:19', '2025-09-11 09:34:19');

-- --------------------------------------------------------

--
-- Structure de la table `disponibilite_camping`
--

DROP TABLE IF EXISTS `disponibilite_camping`;
CREATE TABLE IF NOT EXISTS `disponibilite_camping` (
  `id_disponibilite` int NOT NULL AUTO_INCREMENT,
  `id_camping` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `statut` varchar(50) DEFAULT 'disponible',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_disponibilite`),
  KEY `idx_camping` (`id_camping`),
  KEY `idx_dates` (`date_debut`,`date_fin`),
  KEY `idx_statut` (`statut`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `id_notification` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `contenu` text,
  `date` date NOT NULL,
  `destinataire` int NOT NULL,
  `lu` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notification`),
  KEY `idx_destinataire` (`destinataire`),
  KEY `idx_date` (`date`),
  KEY `idx_lu` (`lu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

DROP TABLE IF EXISTS `paiement`;
CREATE TABLE IF NOT EXISTS `paiement` (
  `id_paiement` int NOT NULL AUTO_INCREMENT,
  `id_reservation` int DEFAULT NULL COMMENT 'Optionnel - NULL pour les paiements d abonnement',
  `type_paiement` enum('reservation','abonnement','amende') NOT NULL DEFAULT 'reservation',
  `montant` float NOT NULL,
  `mode` varchar(50) NOT NULL COMMENT 'ex: carte, paypal, especes',
  `statut` varchar(50) DEFAULT 'en_attente' COMMENT 'paye, echec, rembourse',
  `date_paiement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reference_transaction` varchar(100) DEFAULT NULL COMMENT 'identifiant du paiement',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_paiement`),
  UNIQUE KEY `unique_reference` (`reference_transaction`),
  KEY `idx_reservation` (`id_reservation`),
  KEY `idx_type_paiement` (`type_paiement`),
  KEY `idx_statut` (`statut`),
  KEY `idx_mode` (`mode`),
  KEY `idx_date_paiement` (`date_paiement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `point_interet`
--

DROP TABLE IF EXISTS `point_interet`;
CREATE TABLE IF NOT EXISTS `point_interet` (
  `id_point` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `id_zone` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_point`),
  KEY `idx_zone` (`id_zone`),
  KEY `idx_coordinates` (`latitude`,`longitude`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
CREATE TABLE IF NOT EXISTS `reservation` (
  `id_reservation` int NOT NULL AUTO_INCREMENT,
  `date_reservation` date NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `statut` varchar(50) DEFAULT 'en_attente',
  `id_utilisateur` int NOT NULL,
  `id_camping` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reservation`),
  KEY `idx_utilisateur` (`id_utilisateur`),
  KEY `idx_camping` (`id_camping`),
  KEY `idx_dates` (`date_debut`,`date_fin`),
  KEY `idx_statut` (`statut`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `id_role` int NOT NULL AUTO_INCREMENT,
  `nom_role` varchar(50) NOT NULL,
  `description` text,
  `permissions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_role`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`id_role`, `nom_role`, `description`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrateur du système', 'all', '2025-09-11 09:33:41', '2025-09-11 09:33:41'),
(2, 'guide', 'Guide du parc', 'view_zones,manage_reservations', '2025-09-11 09:33:41', '2025-09-11 09:33:41'),
(3, 'user', 'Utilisateur standard', 'view_zones,make_reservations', '2025-09-11 09:33:41', '2025-09-11 09:33:41');

-- --------------------------------------------------------

--
-- Structure de la table `sentier`
--

DROP TABLE IF EXISTS `sentier`;
CREATE TABLE IF NOT EXISTS `sentier` (
  `id_sentier` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `niveau_difficulte` varchar(50) NOT NULL,
  `description` text,
  `id_zone` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sentier`),
  KEY `idx_zone` (`id_zone`),
  KEY `idx_difficulte` (`niveau_difficulte`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_abonnement`
--

DROP TABLE IF EXISTS `type_abonnement`;
CREATE TABLE IF NOT EXISTS `type_abonnement` (
  `id_type_abonnement` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL COMMENT 'Ex: Mensuel, Annuel, Famille',
  `description` text,
  `duree_mois` int NOT NULL COMMENT 'Durée en mois',
  `prix` decimal(10,2) NOT NULL COMMENT 'Prix de l abonnement',
  `reduction_pourcentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Réduction par défaut (%)',
  `avantages` json DEFAULT NULL COMMENT 'Liste des avantages spécifiques',
  `actif` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_type_abonnement`),
  KEY `idx_actif` (`actif`),
  KEY `idx_prix` (`prix`),
  KEY `idx_duree` (`duree_mois`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `type_abonnement`
--

INSERT INTO `type_abonnement` (`id_type_abonnement`, `nom`, `description`, `duree_mois`, `prix`, `reduction_pourcentage`, `avantages`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'Mensuel Basic', 'Abonnement mensuel avec avantages de base', 1, 15.00, 0.00, '[\"Accès prioritaire\", \"Newsletter\"]', 1, '2025-09-11 09:34:02', '2025-09-11 09:34:02'),
(2, 'Annuel Standard', 'Abonnement annuel avec réductions', 12, 150.00, 15.00, '[\"Accès prioritaire\", \"Réductions camping\", \"Visites guidées gratuites\"]', 1, '2025-09-11 09:34:02', '2025-09-11 09:34:02'),
(3, 'Famille Plus', 'Abonnement famille (2 adultes + enfants)', 12, 250.00, 20.00, '[\"Accès famille\", \"Activités enfants\", \"Camping gratuit 2 nuits\"]', 1, '2025-09-11 09:34:02', '2025-09-11 09:34:02');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires_at` datetime DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `id_role` int DEFAULT NULL,
  `abonnement` tinyint(1) DEFAULT '0',
  `carte_membre_numero` varchar(100) DEFAULT NULL,
  `carte_membre_date_validite` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role_enum` (`role`),
  KEY `idx_role_fk` (`id_role`),
  KEY `idx_active` (`is_active`),
  KEY `idx_remember_token` (`remember_token`),
  KEY `idx_verification_token` (`email_verification_token`(250))
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `email_verified`, `email_verification_token`, `email_verification_expires_at`, `password_hash`, `role`, `id_role`, `abonnement`, `carte_membre_numero`, `carte_membre_date_validite`, `is_active`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Système', 'admin@calanques.fr', 1, NULL, NULL, 'y02IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 0, NULL, NULL, 1, NULL, NULL, '2025-09-11 09:34:11', '2025-09-12 12:58:59'),
(2, 'ajpajkd', 'hamza', 'hamza@hamza.fr', 1, NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=3$cWpIYmZGS3g3bmpPbGUxdQ$ma+dseGJGTZMq5otwC6NihGkxAGkz/D8C4O5pebR1WQ', 'admin', NULL, 0, NULL, NULL, 1, NULL, 'd19c639b198100cf1285c8ad78ddf7f18272ec202bff41ad157cf7e6f1bd85a5', '2025-09-11 09:37:32', '2025-09-12 12:58:59'),
(12, 'msa', 'msa', 'hamza.msa-soilihi@laplateforme.io', 1, NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=3$UWxjc2tkVm1ncTczOGVUSA$hkX8DogBBEo05+GozCuPrw5iGmmQ6dH5YJqKL3UmH0s', 'user', NULL, 0, NULL, NULL, 1, '2025-09-12 13:41:48', NULL, '2025-09-12 13:36:40', '2025-09-12 13:41:48'),
(4, 'lala', 'lala', 'lala@lala.fr', 1, NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=3$eFRKVnc1aFB2NmlWNnIueA$wfyqqTV/iZvVPBWYtnHSt44osnMjx39oCGgvOFjGQbw', 'user', NULL, 0, NULL, NULL, 1, NULL, NULL, '2025-09-12 08:29:09', '2025-09-12 12:58:59'),
(5, 'baba', 'baba', 'baba@ba.fr', 1, NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=3$SGsxZjlGZ2t3MkgvbXVrNQ$3fur8JBOKCKZJQBIiEHm2I0Zsw0gf8P/1lbeC5+vIj8', 'user', NULL, 0, NULL, NULL, 1, NULL, NULL, '2025-09-12 08:46:11', '2025-09-12 12:58:59'),
(6, 'mama', 'mama', 'mama@mama.fr', 1, NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=3$RVQ0ZTAxODVxNGExMDFTcg$JydBU2bYwEGpGGMPiQH7Zs6hdIpl/Zy0aJ2cM8xuJvY', 'user', NULL, 0, NULL, NULL, 1, NULL, NULL, '2025-09-12 11:36:05', '2025-09-12 12:58:59'),
(22, 'Maho', 'Mahela', 'mahela.mao@gmail.com', 0, '65c29ffb22f867837e6b4fa4d4574fdd66cab543b4c27f73eaebbb23fecd4ef4', '2025-09-16 10:14:41', '$argon2id$v=19$m=65536,t=4,p=3$SlhXU0wvNmZzd2wzM2ZOQg$YORCZn8Yw5mYNa7HwC1tAGt6QKVYU/LoMLoumK/EsiM', 'user', NULL, 0, NULL, NULL, 1, NULL, NULL, '2025-09-15 10:14:41', '2025-09-15 10:14:41'),
(23, 'ejfheoé', 'efjrl', 'msahamza738@gmail.com', 1, NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=3$dFk4ZEVONlo0UFlSRTB3WQ$h3zY2ZHuYWqXLkspWdu2Yqd02JPs/mP/a1O9i1Nd1yM', 'user', NULL, 0, NULL, NULL, 1, '2025-09-15 10:16:54', NULL, '2025-09-15 10:16:32', '2025-09-15 10:16:54');

-- --------------------------------------------------------

--
-- Structure de la table `zone`
--

DROP TABLE IF EXISTS `zone`;
CREATE TABLE IF NOT EXISTS `zone` (
  `id_zone` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `superficie` float DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_zone`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `zone`
--

INSERT INTO `zone` (`id_zone`, `nom`, `superficie`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Calanque de Sormiou', 150.5, 'Une des plus belles calanques du parc', '2025-09-11 09:33:53', '2025-09-11 09:33:53'),
(2, 'Calanque de Morgiou', 120.3, 'Calanque avec port de pêche traditionnel', '2025-09-11 09:33:53', '2025-09-11 09:33:53'),
(3, 'Calanque d En-Vau', 95.7, 'Calanque aux eaux turquoise', '2025-09-11 09:33:53', '2025-09-11 09:33:53'),
(4, 'Massif des Calanques', 8500, 'Zone principale du parc national', '2025-09-11 09:33:53', '2025-09-11 09:33:53');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
