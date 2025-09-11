-- =====================================================
-- MIGRATION 003: Système de camping et réservations
-- Parc National des Calanques
-- =====================================================

USE `le-parc-national-des-calanques`;

SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer les tables si elles existent
DROP TABLE IF EXISTS Reservation;
DROP TABLE IF EXISTS Disponibilite_Camping;
DROP TABLE IF EXISTS Camping;

-- Table Camping
CREATE TABLE Camping (
    id_camping INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    capacite INT DEFAULT 0,
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
    statut VARCHAR(50) DEFAULT 'disponible',
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
    statut VARCHAR(50) DEFAULT 'en_attente',
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

-- Insérer les campings de base
INSERT INTO Camping (nom, capacite, localisation) VALUES
('Camping Sormiou', 50, 'Calanque de Sormiou'),
('Camping Morgiou', 35, 'Calanque de Morgiou'),
('Camping En-Vau', 25, 'Calanque d''En-Vau');

SET FOREIGN_KEY_CHECKS = 1;