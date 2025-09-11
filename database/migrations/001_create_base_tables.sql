-- =====================================================
-- MIGRATION 001: Création des tables de base
-- Parc National des Calanques
-- =====================================================

USE `le-parc-national-des-calanques`;

SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer les tables si elles existent
DROP TABLE IF EXISTS Zone;
DROP TABLE IF EXISTS Role;

-- Table Zone
CREATE TABLE Zone (
    id_zone INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    superficie FLOAT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table Role
CREATE TABLE Role (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nom_role VARCHAR(50) NOT NULL,
    description TEXT,
    permissions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insérer les données de base
INSERT INTO Role (nom_role, description, permissions) VALUES
('admin', 'Administrateur du système', 'all'),
('guide', 'Guide du parc', 'view_zones,manage_reservations'),
('user', 'Utilisateur standard', 'view_zones,make_reservations');

INSERT INTO Zone (nom, superficie, description) VALUES
('Calanque de Sormiou', 150.5, 'Une des plus belles calanques du parc'),
('Calanque de Morgiou', 120.3, 'Calanque avec port de pêche traditionnel'),
('Calanque d''En-Vau', 95.7, 'Calanque aux eaux turquoise'),
('Massif des Calanques', 8500.0, 'Zone principale du parc national');

SET FOREIGN_KEY_CHECKS = 1;