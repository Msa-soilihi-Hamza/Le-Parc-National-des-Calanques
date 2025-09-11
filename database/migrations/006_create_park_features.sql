-- =====================================================
-- MIGRATION 006: Fonctionnalités du parc
-- Parc National des Calanques
-- =====================================================

USE `le-parc-national-des-calanques`;

SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer les tables si elles existent
DROP TABLE IF EXISTS Notification;
DROP TABLE IF EXISTS Ressource_Naturelle;
DROP TABLE IF EXISTS Point_Interet;
DROP TABLE IF EXISTS Sentier;

-- Table Sentier
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

-- Table Point_Interet
CREATE TABLE Point_Interet (
    id_point INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    latitude FLOAT,
    longitude FLOAT,
    id_zone INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_zone) REFERENCES Zone(id_zone) ON DELETE CASCADE,
    INDEX idx_zone (id_zone),
    INDEX idx_coordinates (latitude, longitude)
);

-- Table Ressource_Naturelle
CREATE TABLE Ressource_Naturelle (
    id_ressource INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    etat VARCHAR(100),
    id_zone INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_zone) REFERENCES Zone(id_zone) ON DELETE CASCADE,
    INDEX idx_zone (id_zone),
    INDEX idx_type (type),
    INDEX idx_etat (etat)
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

SET FOREIGN_KEY_CHECKS = 1;