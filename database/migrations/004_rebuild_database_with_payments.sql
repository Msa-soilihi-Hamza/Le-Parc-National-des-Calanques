-- Migration 004: Reconstruction complète de la base avec système de paiement
-- Date: 2025-09-05

-- Supprimer toutes les tables existantes (ordre inverse des dépendances)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notification;
DROP TABLE IF EXISTS ressource_naturelle;
DROP TABLE IF EXISTS disponibilite_camping;
DROP TABLE IF EXISTS express_point_interet;
DROP TABLE IF EXISTS reservation;
DROP TABLE IF EXISTS paiement;
DROP TABLE IF EXISTS camping;
DROP TABLE IF EXISTS sentier;
DROP TABLE IF EXISTS point_interet;
DROP TABLE IF EXISTS utilisateur;
DROP TABLE IF EXISTS zone;
SET FOREIGN_KEY_CHECKS = 1;

-- Table Zone (créée en premier car référencée par d'autres)
CREATE TABLE Zone (
    id_zone INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    superficie FLOAT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table Utilisateur
CREATE TABLE Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(191) NOT NULL,
    mot_passe VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'visiteur',
    abonnement BOOLEAN DEFAULT FALSE,
    carte_membre_numero VARCHAR(100),
    carte_membre_date_validite DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email),
    INDEX idx_role (role)
);

-- Table Sentier (structure simplifiée)
CREATE TABLE Sentier (
    id_sentier INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    niveau_difficulte VARCHAR(50) NOT NULL,
    description TEXT,
    id_zone INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_zone) REFERENCES Zone(id_zone) ON DELETE CASCADE,
    INDEX idx_zone (id_zone),
    INDEX idx_difficulte (niveau_difficulte)
);

-- Table Point_Interet (structure indépendante)
CREATE TABLE Point_Interet (
    id_point INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    latitude FLOAT,
    longitude FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table Camping
CREATE TABLE Camping (
    id_camping INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    capacite INT NOT NULL DEFAULT 0,
    localisation VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table Disponibilite_Camping
CREATE TABLE Disponibilite_Camping (
    id_disponibilite INT AUTO_INCREMENT PRIMARY KEY,
    id_camping INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_camping) REFERENCES Camping(id_camping) ON DELETE CASCADE,
    INDEX idx_camping (id_camping),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_statut (statut)
);

-- Table Reservation
CREATE TABLE Reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    date_reservation DATE NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'en_attente',
    id_utilisateur INT NOT NULL,
    id_camping INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_camping) REFERENCES Camping(id_camping) ON DELETE CASCADE,
    INDEX idx_utilisateur (id_utilisateur),
    INDEX idx_camping (id_camping),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_statut (statut)
);

-- Table Paiement (NOUVELLE)
CREATE TABLE Paiement (
    id_paiement INT AUTO_INCREMENT PRIMARY KEY,
    id_reservation INT NOT NULL,
    montant FLOAT NOT NULL,
    mode VARCHAR(50) NOT NULL COMMENT 'ex: carte, paypal, espèces',
    statut VARCHAR(50) NOT NULL DEFAULT 'en_attente' COMMENT 'payé, échoué, remboursé',
    date_paiement TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reference_transaction VARCHAR(100) COMMENT 'identifiant du paiement',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_reservation) REFERENCES Reservation(id_reservation) ON DELETE CASCADE,
    INDEX idx_reservation (id_reservation),
    INDEX idx_statut (statut),
    INDEX idx_mode (mode),
    INDEX idx_date_paiement (date_paiement),
    UNIQUE KEY unique_reference (reference_transaction)
);

-- Table Ressource_Naturelle
CREATE TABLE Ressource_Naturelle (
    id_ressource INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    etat VARCHAR(100),
    id_zone INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_zone) REFERENCES Zone(id_zone) ON DELETE SET NULL,
    INDEX idx_zone (id_zone),
    INDEX idx_type (type)
);

-- Table Notification
CREATE TABLE Notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT,
    date DATE NOT NULL,
    destinataire INT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destinataire) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    INDEX idx_destinataire (destinataire),
    INDEX idx_date (date),
    INDEX idx_lu (lu)
);