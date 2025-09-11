-- Création des tables étape par étape
USE `le-parc-national-des-calanques`;

-- Désactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Créer la table Zone d'abord
DROP TABLE IF EXISTS Zone;
CREATE TABLE Zone (
    id_zone INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    superficie FLOAT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
SELECT 'Table Zone créée' as status;

-- 2. Créer la table Role
DROP TABLE IF EXISTS Role;
CREATE TABLE Role (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nom_role VARCHAR(50) NOT NULL,
    description TEXT,
    permissions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
SELECT 'Table Role créée' as status;

-- 3. Créer la table Utilisateur
DROP TABLE IF EXISTS Utilisateur;
CREATE TABLE Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    id_role INT,
    abonnement BOOLEAN DEFAULT FALSE,
    carte_membre_numero VARCHAR(100),
    carte_membre_date_validite DATE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_role) REFERENCES Role(id_role) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_role_enum (role),
    INDEX idx_role_fk (id_role),
    INDEX idx_active (is_active),
    INDEX idx_remember_token (remember_token)
);
SELECT 'Table Utilisateur créée' as status;

-- Vérifier les tables créées
SHOW TABLES;

-- Réactiver les vérifications
SET FOREIGN_KEY_CHECKS = 1;